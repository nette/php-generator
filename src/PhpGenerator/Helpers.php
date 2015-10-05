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
	const PHP_IDENT = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
	const MAX_DEPTH = 50;


	/**
	 * Returns a PHP representation of a variable.
	 * @return string
	 */
	public static function dump($var)
	{
		return self::_dump($var);
	}


	private static function _dump(& $var, $level = 0)
	{
		if ($var instanceof PhpLiteral) {
			return (string) $var;

		} elseif (is_float($var)) {
			$var = var_export($var, TRUE);
			return strpos($var, '.') === FALSE ? $var . '.0' : $var;

		} elseif (is_bool($var)) {
			return $var ? 'TRUE' : 'FALSE';

		} elseif (is_string($var) && (preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || preg_last_error())) {
			static $table;
			if ($table === NULL) {
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
			if ($marker === NULL) {
				$marker = uniqid("\x00", TRUE);
			}
			if (empty($var)) {
				$out = '';

			} elseif ($level > self::MAX_DEPTH || isset($var[$marker])) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$out = '';
				$outAlt = "\n$space";
				$var[$marker] = TRUE;
				$counter = 0;
				foreach ($var as $k => & $v) {
					if ($k !== $marker) {
						$item = ($k === $counter ? '' : self::_dump($k, $level + 1) . ' => ') . self::_dump($v, $level + 1);
						$counter = is_int($k) ? max($k + 1, $counter) : $counter;
						$out .= ($out === '' ? '' : ', ') . $item;
						$outAlt .= "\t$item,\n$space";
					}
				}
				unset($var[$marker]);
			}
			return 'array(' . (strpos($out, "\n") === FALSE && strlen($out) < 40 ? $out : $outAlt) . ')';

		} elseif ($var instanceof \Serializable) {
			$var = serialize($var);
			return 'unserialize(' . self::_dump($var, $level) . ')';

		} elseif ($var instanceof \Closure) {
			throw new Nette\InvalidArgumentException('Cannot dump closure.');

		} elseif (is_object($var)) {
			if (PHP_VERSION_ID >= 70000 && ($rc = new \ReflectionObject($var)) && $rc->isAnonymous()) {
				throw new Nette\InvalidArgumentException('Cannot dump anonymous class.');
			}
			$arr = (array) $var;
			$space = str_repeat("\t", $level);
			$class = get_class($var);

			static $list = array();
			if ($level > self::MAX_DEPTH || in_array($var, $list, TRUE)) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$out = "\n";
				$list[] = $var;
				if (method_exists($var, '__sleep')) {
					foreach ($var->__sleep() as $v) {
						$props[$v] = $props["\x00*\x00$v"] = $props["\x00$class\x00$v"] = TRUE;
					}
				}
				foreach ($arr as $k => & $v) {
					if (!isset($props) || isset($props[$k])) {
						$out .= "$space\t" . self::_dump($k, $level + 1) . ' => ' . self::_dump($v, $level + 1) . ",\n";
					}
				}
				array_pop($list);
				$out .= $space;
			}
			return $class === 'stdClass'
				? "(object) array($out)"
				: __CLASS__ . "::createObject('$class', array($out))";

		} elseif (is_resource($var)) {
			throw new Nette\InvalidArgumentException('Cannot dump resource.');

		} else {
			return var_export($var, TRUE);
		}
	}


	/**
	 * Generates PHP statement.
	 * @return string
	 */
	public static function format($statement)
	{
		$args = func_get_args();
		return self::formatArgs(array_shift($args), $args);
	}


	/**
	 * Generates PHP statement.
	 * @return string
	 */
	public static function formatArgs($statement, array $args)
	{
		$a = strpos($statement, '?');
		while ($a !== FALSE) {
			if (!$args) {
				throw new Nette\InvalidArgumentException('Insufficient number of arguments.');
			}
			$arg = array_shift($args);
			if (substr($statement, $a + 1, 1) === '*') { // ?*
				if (!is_array($arg)) {
					throw new Nette\InvalidArgumentException('Argument must be an array.');
				}
				$s = substr($statement, 0, $a);
				$sep = '';
				foreach ($arg as $tmp) {
					$s .= $sep . self::dump($tmp);
					$sep = strlen($s) - strrpos($s, "\n") > 100 ? ",\n\t" : ', ';
				}
				$statement = $s . substr($statement, $a + 2);
				$a = strlen($s);

			} else {
				$arg = substr($statement, $a - 1, 1) === '$' || in_array(substr($statement, $a - 2, 2), array('->', '::'), TRUE)
					? self::formatMember($arg) : self::_dump($arg);
				$statement = substr_replace($statement, $arg, $a, 1);
				$a += strlen($arg);
			}
			$a = strpos($statement, '?', $a);
		}
		return $statement;
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
	 * @return bool
	 */
	public static function isIdentifier($value)
	{
		return is_string($value) && preg_match('#^' . self::PHP_IDENT . '\z#', $value);
	}


	/** @internal */
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
		return ($pos = strrpos($name, '\\')) === FALSE ? $name : substr($name, $pos + 1);
	}

}
