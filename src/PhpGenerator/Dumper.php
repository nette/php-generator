<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * PHP code generator utils.
 */
final class Dumper
{
	private const IndentLength = 4;

	public int $maxDepth = 50;
	public int $wrapLength = 120;
	public string $indentation = "\t";
	public bool $customObjects = true;


	/**
	 * Returns a PHP representation of a variable.
	 */
	public function dump(mixed $var, int $column = 0): string
	{
		return $this->dumpVar($var, [], 0, $column);
	}


	/** @param  array<mixed[]|object>  $parents */
	private function dumpVar(mixed &$var, array $parents = [], int $level = 0, int $column = 0): string
	{
		if ($var === null) {
			return 'null';

		} elseif (is_string($var)) {
			return $this->dumpString($var);

		} elseif (is_array($var)) {
			return $this->dumpArray($var, $parents, $level, $column);

		} elseif ($var instanceof Literal) {
			return $this->dumpLiteral($var, $level);

		} elseif (is_object($var)) {
			return $this->dumpObject($var, $parents, $level, $column);

		} elseif (is_resource($var)) {
			throw new Nette\InvalidStateException('Cannot dump value of type resource.');

		} else {
			return var_export($var, return: true);
		}
	}


	private function dumpString(string $s): string
	{
		$special = [
			"\r" => '\r',
			"\n" => '\n',
			"\t" => '\t',
			"\e" => '\e',
			'\\' => '\\\\',
		];

		$utf8 = preg_match('##u', $s);
		$escaped = preg_replace_callback(
			$utf8 ? '#[\p{C}\\\\]#u' : '#[\x00-\x1F\x7F-\xFF\\\\]#',
			fn($m) => $special[$m[0]] ?? (strlen($m[0]) === 1
					? '\x' . str_pad(strtoupper(dechex(ord($m[0]))), 2, '0', STR_PAD_LEFT) . ''
					: '\u{' . strtoupper(ltrim(dechex(self::utf8Ord($m[0])), '0')) . '}'),
			$s,
		);
		return $s === str_replace('\\\\', '\\', $escaped)
			? "'" . preg_replace('#\'|\\\\(?=[\'\\\\]|$)#D', '\\\\$0', $s) . "'"
			: '"' . addcslashes($escaped, '"$') . '"';
	}


	private static function utf8Ord(string $c): int
	{
		$ord0 = ord($c[0]);
		return match (true) {
			$ord0 < 0x80 => $ord0,
			$ord0 < 0xE0 => ($ord0 << 6) + ord($c[1]) - 0x3080,
			$ord0 < 0xF0 => ($ord0 << 12) + (ord($c[1]) << 6) + ord($c[2]) - 0xE2080,
			default => ($ord0 << 18) + (ord($c[1]) << 12) + (ord($c[2]) << 6) + ord($c[3]) - 0x3C82080,
		};
	}


	/**
	 * @param  mixed[]  $var
	 * @param  array<mixed[]|object>  $parents
	 */
	private function dumpArray(array &$var, array $parents, int $level, int $column): string
	{
		if (empty($var)) {
			return '[]';

		} elseif ($level > $this->maxDepth || in_array($var, $parents, strict: true)) {
			throw new Nette\InvalidStateException('Nesting level too deep or recursive dependency.');
		}

		$space = str_repeat($this->indentation, $level);
		$outInline = '';
		$outWrapped = "\n$space";
		$parents[] = $var;
		$counter = 0;
		$hideKeys = is_int(($tmp = array_keys($var))[0]) && $tmp === range($tmp[0], $tmp[0] + count($var) - 1);

		foreach ($var as $k => &$v) {
			$keyPart = $hideKeys && $k === $counter
				? ''
				: $this->dumpVar($k) . ' => ';
			$counter = is_int($k) ? max($k + 1, $counter) : $counter;
			$outInline .= ($outInline === '' ? '' : ', ') . $keyPart;
			$outInline .= $this->dumpVar($v, $parents, 0, $column + strlen($outInline));
			$outWrapped .= $this->indentation
				. $keyPart
				. $this->dumpVar($v, $parents, $level + 1, strlen($keyPart))
				. ",\n$space";
		}

		array_pop($parents);
		$wrap = str_contains($outInline, "\n") || $level * self::IndentLength + $column + strlen($outInline) + 3 > $this->wrapLength; // 3 = [],
		return '[' . ($wrap ? $outWrapped : $outInline) . ']';
	}


	/** @param  array<mixed[]|object>  $parents */
	private function dumpObject(object $var, array $parents, int $level, int $column): string
	{
		if ($level > $this->maxDepth || in_array($var, $parents, strict: true)) {
			throw new Nette\InvalidStateException('Nesting level too deep or recursive dependency.');
		}

		$class = $var::class;
		$parents[] = $var;

		if ($class === \stdClass::class) {
			$var = (array) $var;
			return '(object) ' . $this->dumpArray($var, $parents, $level, $column + 10);

		} elseif ($class === \DateTime::class || $class === \DateTimeImmutable::class) {
			return $this->format(
				"new \\$class(?, new \\DateTimeZone(?))",
				$var->format('Y-m-d H:i:s.u'),
				$var->getTimeZone()->getName(),
			);

		} elseif ($var instanceof \UnitEnum) {
			return '\\' . $var::class . '::' . $var->name;

		} elseif ($var instanceof \Closure) {
			$inner = Nette\Utils\Callback::unwrap($var);
			if (Nette\Utils\Callback::isStatic($inner)) {
				return PHP_VERSION_ID < 80100
					? '\Closure::fromCallable(' . $this->dump($inner) . ')'
					: implode('::', (array) $inner) . '(...)';
			}

			throw new Nette\InvalidStateException('Cannot dump object of type Closure.');

		} elseif ($this->customObjects) {
			return $this->dumpCustomObject($var, $parents, $level);

		} else {
			throw new Nette\InvalidStateException("Cannot dump object of type $class.");
		}
	}


	/** @param  array<mixed[]|object>  $parents */
	private function dumpCustomObject(object $var, array $parents, int $level): string
	{
		if ((new \ReflectionObject($var))->isAnonymous()) {
			throw new Nette\InvalidStateException('Cannot dump an instance of an anonymous class.');
		}

		$class = $var::class;
		$space = str_repeat($this->indentation, $level);
		$out = "\n";

		if (method_exists($var, '__serialize')) {
			$arr = $var->__serialize();
		} else {
			$arr = (array) $var;
			if (method_exists($var, '__sleep')) {
				foreach ($var->__sleep() as $v) {
					$props[$v] = $props["\x00*\x00$v"] = $props["\x00$class\x00$v"] = true;
				}
			}
		}

		foreach ($arr as $k => &$v) {
			if (!isset($props) || isset($props[$k])) {
				$out .= $space . $this->indentation
					. ($keyPart = $this->dumpVar($k) . ' => ')
					. $this->dumpVar($v, $parents, $level + 1, strlen($keyPart))
					. ",\n";
			}
		}

		return '\\' . self::class . "::createObject(\\$class::class, [$out$space])";
	}


	private function dumpLiteral(Literal $var, int $level): string
	{
		$s = $var->formatWith($this);
		$s = Nette\Utils\Strings::normalizeNewlines($s);
		$s = Nette\Utils\Strings::indent(trim($s), $level, $this->indentation);
		return ltrim($s, $this->indentation);
	}


	/**
	 * Generates PHP statement. Supports placeholders: ?  \?  $?  ->?  ::?  ...?  ...?:  ?*
	 */
	public function format(string $statement, mixed ...$args): string
	{
		$tokens = preg_split('#(\.\.\.\?:?|\$\?|->\?|::\?|\\\\\?|\?\*|\?(?!\w))#', $statement, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = '';
		foreach ($tokens as $n => $token) {
			if ($n % 2 === 0) {
				$res .= $token;
			} elseif ($token === '\?') {
				$res .= '?';
			} elseif (!$args) {
				throw new Nette\InvalidArgumentException('Insufficient number of arguments.');
			} elseif ($token === '?') {
				$res .= $this->dump(array_shift($args), strlen($res) - strrpos($res, "\n"));
			} elseif ($token === '...?' || $token === '...?:' || $token === '?*') {
				$arg = array_shift($args);
				if (!is_array($arg)) {
					throw new Nette\InvalidArgumentException('Argument must be an array.');
				}

				$res .= $this->dumpArguments($arg, strlen($res) - strrpos($res, "\n"), $token === '...?:');

			} else { // $  ->  ::
				$arg = array_shift($args);
				if ($arg instanceof Literal || !Helpers::isIdentifier($arg)) {
					$arg = '{' . $this->dumpVar($arg) . '}';
				}

				$res .= substr($token, 0, -1) . $arg;
			}
		}

		if ($args) {
			throw new Nette\InvalidArgumentException('Insufficient number of placeholders.');
		}

		return $res;
	}


	/** @param  mixed[]  $var */
	private function dumpArguments(array &$var, int $column, bool $named): string
	{
		$outInline = $outWrapped = '';

		foreach ($var as $k => &$v) {
			$k = !$named || is_int($k) ? '' : $k . ': ';
			$outInline .= $outInline === '' ? '' : ', ';
			$outInline .= $k . $this->dumpVar($v, [$var], 0, $column + strlen($outInline));
			$outWrapped .= "\n" . $this->indentation . $k . $this->dumpVar($v, [$var], 1) . ',';
		}

		return count($var) > 1 && (str_contains($outInline, "\n") || $column + strlen($outInline) > $this->wrapLength)
			? $outWrapped . "\n"
			: $outInline;
	}


	/**
	 * @param  mixed[]  $props
	 * @internal
	 */
	public static function createObject(string $class, array $props): object
	{
		if (method_exists($class, '__serialize')) {
			$obj = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
			$obj->__unserialize($props);
			return $obj;
		}
		return unserialize('O' . substr(serialize($class), 1, -1) . substr(serialize($props), 1));
	}
}
