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
	public const WRAP_LENGTH = 100;

	private const INDENT_LENGTH = 4;

	private const MAX_DEPTH = 50;


	/**
	 * Returns a PHP representation of a variable.
	 */
	public function dump($var): string
	{
		return $this->dumpVar($var);
	}


	private function dumpVar(&$var, int $level = 0): string
	{
		if ($var instanceof PhpLiteral) {
			return (string) $var;

		} elseif ($var === null) {
			return 'null';

		} elseif (is_string($var)) {
			return $this->dumpString($var);

		} elseif (is_array($var)) {
			return $this->dumpArray($var, $level);

		} elseif (is_object($var)) {
			return $this->dumpObject($var, $level);

		} elseif (is_resource($var)) {
			throw new Nette\InvalidArgumentException('Cannot dump resource.');

		} else {
			return var_export($var, true);
		}
	}


	private function dumpString(string $var): string
	{
		if (preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || preg_last_error()) {
			static $table;
			if ($table === null) {
				foreach (array_merge(range("\x00", "\x1F"), range("\x7F", "\xFF")) as $ch) {
					$table[$ch] = '\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
				}
				$table['\\'] = '\\\\';
				$table["\r"] = '\r';
				$table["\n"] = '\n';
				$table["\t"] = '\t';
				$table['$'] = '\$';
				$table['"'] = '\"';
			}
			return '"' . strtr($var, $table) . '"';
		}

		return "'" . preg_replace('#\'|\\\\(?=[\'\\\\]|$)#D', '\\\\$0', $var) . "'";
	}


	private function dumpArray(array &$var, int $level): string
	{
		static $marker;
		if ($marker === null) {
			$marker = uniqid("\x00", true);
		}
		if (empty($var)) {
			return '[]';

		} elseif ($level > self::MAX_DEPTH || isset($var[$marker])) {
			throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');
		}

		$space = str_repeat("\t", $level);
		$outInline = '';
		$outWrapped = "\n$space";
		$var[$marker] = true;
		$counter = 0;

		foreach ($var as $k => &$v) {
			if ($k !== $marker) {
				$item = ($k === $counter ? '' : $this->dumpVar($k, $level + 1) . ' => ') . $this->dumpVar($v, $level + 1);
				$counter = is_int($k) ? max($k + 1, $counter) : $counter;
				$outInline .= ($outInline === '' ? '' : ', ') . $item;
				$outWrapped .= "\t$item,\n$space";
			}
		}

		unset($var[$marker]);
		$wrap = strpos($outInline, "\n") !== false || strlen($outInline) > self::WRAP_LENGTH - $level * self::INDENT_LENGTH;
		return '[' . ($wrap ? $outWrapped : $outInline) . ']';
	}


	private function dumpObject(&$var, int $level): string
	{
		if ($var instanceof \Serializable) {
			return 'unserialize(' . $this->dumpString(serialize($var)) . ')';

		} elseif ($var instanceof \Closure) {
			throw new Nette\InvalidArgumentException('Cannot dump closure.');
		}

		$class = get_class($var);
		if ((new \ReflectionObject($var))->isAnonymous()) {
			throw new Nette\InvalidArgumentException('Cannot dump anonymous class.');

		} elseif (in_array($class, ['DateTime', 'DateTimeImmutable'], true)) {
			return $this->format("new $class(?, new DateTimeZone(?))", $var->format('Y-m-d H:i:s.u'), $var->getTimeZone()->getName());
		}

		$arr = (array) $var;
		$space = str_repeat("\t", $level);

		static $list = [];
		if ($level > self::MAX_DEPTH || in_array($var, $list, true)) {
			throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');
		}

		$out = "\n";
		$list[] = $var;
		if (method_exists($var, '__sleep')) {
			foreach ($var->__sleep() as $v) {
				$props[$v] = $props["\x00*\x00$v"] = $props["\x00$class\x00$v"] = true;
			}
		}

		foreach ($arr as $k => &$v) {
			if (!isset($props) || isset($props[$k])) {
				$out .= "$space\t" . $this->dumpVar($k, $level + 1) . ' => ' . $this->dumpVar($v, $level + 1) . ",\n";
			}
		}

		array_pop($list);
		$out .= $space;
		return $class === 'stdClass'
			? "(object) [$out]"
			: __CLASS__ . "::createObject('$class', [$out])";
	}


	/**
	 * Generates PHP statement.
	 */
	public function format(string $statement, ...$args): string
	{
		$tokens = preg_split('#(\.\.\.\?|\$\?|->\?|::\?|\\\\\?|\?\*|\?)#', $statement, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = '';
		foreach ($tokens as $n => $token) {
			if ($n % 2 === 0) {
				$res .= $token;
			} elseif ($token === '\\?') {
				$res .= '?';
			} elseif (!$args) {
				throw new Nette\InvalidArgumentException('Insufficient number of arguments.');
			} elseif ($token === '?') {
				$res .= $this->dump(array_shift($args));
			} elseif ($token === '...?' || $token === '?*') {
				$arg = array_shift($args);
				if (!is_array($arg)) {
					throw new Nette\InvalidArgumentException('Argument must be an array.');
				}
				$items = [];
				foreach ($arg as $tmp) {
					$items[] = $this->dump($tmp);
				}
				$res .= strlen($tmp = implode(', ', $items)) > self::WRAP_LENGTH && count($items) > 1
					? "\n" . Nette\Utils\Strings::indent(implode(",\n", $items)) . "\n"
					: $tmp;

			} else { // $  ->  ::
				$arg = array_shift($args);
				if ($arg instanceof PhpLiteral || !Helpers::isIdentifier($arg)) {
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


	/**
	 * @return object
	 * @internal
	 */
	public static function createObject(string $class, array $props)
	{
		return unserialize('O' . substr(serialize($class), 1, -1) . substr(serialize($props), 1));
	}
}
