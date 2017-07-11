<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * PHP code generator utils.
 */
class Helpers
{
	use Nette\StaticClass;

	const PHP_IDENT = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
	const MAX_DEPTH = 50;
	const WRAP_LENGTH = 70;


	/**
	 * Returns a PHP representation of a variable.
	 * @return string
	 */
	public static function dump($var)
	{
		return self::_dump($var);
	}


	private static function _dump(&$var, $level = 0)
	{
		if ($var instanceof PhpLiteral) {
			return (string) $var;

		} elseif (is_float($var)) {
			if (is_finite($var)) {
				$var = var_export($var, true);
				return strpos($var, '.') === false ? $var . '.0' : $var; // workaround for PHP < 7.0.2
			}
			return str_replace('.0', '', var_export($var, true)); // workaround for PHP 7.0.2

		} elseif ($var === null) {
			return 'null';

		} elseif (is_string($var) && (preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || preg_last_error())) {
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

		} elseif (is_string($var)) {
			return "'" . preg_replace('#\'|\\\\(?=[\'\\\\]|\z)#', '\\\\$0', $var) . "'";

		} elseif (is_array($var)) {
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === null) {
				$marker = uniqid("\x00", true);
			}
			if (empty($var)) {
				$out = '';

			} elseif ($level > self::MAX_DEPTH || isset($var[$marker])) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$out = '';
				$outAlt = "\n$space";
				$var[$marker] = true;
				$counter = 0;
				foreach ($var as $k => &$v) {
					if ($k !== $marker) {
						$item = ($k === $counter ? '' : self::_dump($k, $level + 1) . ' => ') . self::_dump($v, $level + 1);
						$counter = is_int($k) ? max($k + 1, $counter) : $counter;
						$out .= ($out === '' ? '' : ', ') . $item;
						$outAlt .= "\t$item,\n$space";
					}
				}
				unset($var[$marker]);
			}
			return '[' . (strpos($out, "\n") === false && strlen($out) < self::WRAP_LENGTH ? $out : $outAlt) . ']';

		} elseif ($var instanceof \Serializable) {
			$var = serialize($var);
			return 'unserialize(' . self::_dump($var, $level) . ')';

		} elseif ($var instanceof \Closure) {
			throw new Nette\InvalidArgumentException('Cannot dump closure.');

		} elseif (is_object($var)) {
			$class = get_class($var);
			if (PHP_VERSION_ID >= 70000 && (new \ReflectionObject($var))->isAnonymous()) {
				throw new Nette\InvalidArgumentException('Cannot dump anonymous class.');

			} elseif (in_array($class, ['DateTime', 'DateTimeImmutable'], true)) {
				return self::formatArgs("new $class(?, new DateTimeZone(?))", [$var->format('Y-m-d H:i:s.u'), $var->getTimeZone()->getName()]);
			}

			$arr = (array) $var;
			$space = str_repeat("\t", $level);

			static $list = [];
			if ($level > self::MAX_DEPTH || in_array($var, $list, true)) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$out = "\n";
				$list[] = $var;
				if (method_exists($var, '__sleep')) {
					foreach ($var->__sleep() as $v) {
						$props[$v] = $props["\x00*\x00$v"] = $props["\x00$class\x00$v"] = true;
					}
				}
				foreach ($arr as $k => &$v) {
					if (!isset($props) || isset($props[$k])) {
						$out .= "$space\t" . self::_dump($k, $level + 1) . ' => ' . self::_dump($v, $level + 1) . ",\n";
					}
				}
				array_pop($list);
				$out .= $space;
			}
			return $class === 'stdClass'
				? "(object) [$out]"
				: __CLASS__ . "::createObject('$class', [$out])";

		} elseif (is_resource($var)) {
			throw new Nette\InvalidArgumentException('Cannot dump resource.');

		} else {
			return var_export($var, true);
		}
	}


	/**
	 * Generates PHP statement.
	 * @param  string
	 * @return string
	 */
	public static function format($statement, ...$args)
	{
		return self::formatArgs($statement, $args);
	}


	/**
	 * Generates PHP statement.
	 * @param  string
	 * @return string
	 */
	public static function formatArgs($statement, array $args)
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
				$res .= self::dump(array_shift($args));
			} elseif ($token === '...?' || $token === '?*') {
				$arg = array_shift($args);
				if (!is_array($arg)) {
					throw new Nette\InvalidArgumentException('Argument must be an array.');
				}
				$sep = '';
				foreach ($arg as $tmp) {
					$res .= $sep . self::dump($tmp);
					$sep = strlen($res) - strrpos($res, "\n") > self::WRAP_LENGTH ? ",\n\t" : ', ';
				}
			} else { // $  ->  ::
				$res .= substr($token, 0, -1) . self::formatMember(array_shift($args));
			}
		}
		return $res;
	}


	/**
	 * Returns a PHP representation of a object member.
	 * @return string
	 */
	public static function formatMember($name)
	{
		return $name instanceof PhpLiteral || !self::isIdentifier($name)
			? '{' . self::_dump($name) . '}'
			: $name;
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function formatDocComment($content)
	{
		if (($s = trim($content)) === '') {
			return '';
		} elseif (strpos($content, "\n") === false) {
			return "/** $s */\n";
		} else {
			return str_replace("\n", "\n * ", "/**\n$s") . "\n */\n";
		}
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function unformatDocComment($comment)
	{
		return preg_replace('#^\s*\* ?#m', '', trim(trim(trim($comment), '/*')));
	}


	/**
	 * @return bool
	 */
	public static function isIdentifier($value)
	{
		return is_string($value) && preg_match('#^' . self::PHP_IDENT . '\z#', $value);
	}


	/**
	 * @return bool
	 */
	public static function isNamespaceIdentifier($value, $allowLeadingSlash = false)
	{
		$re = '#^' . ($allowLeadingSlash ? '\\\\?' : '') . self::PHP_IDENT . '(\\\\' . self::PHP_IDENT . ')*\z#';
		return is_string($value) && preg_match($re, $value);
	}


	/**
	 * @param  string
	 * @return object
	 * @internal
	 */
	public static function createObject($class, array $props)
	{
		return unserialize('O' . substr(serialize((string) $class), 1, -1) . substr(serialize($props), 1));
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function extractNamespace($name)
	{
		return ($pos = strrpos($name, '\\')) ? substr($name, 0, $pos) : '';
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function extractShortName($name)
	{
		return ($pos = strrpos($name, '\\')) === false ? $name : substr($name, $pos + 1);
	}
}
