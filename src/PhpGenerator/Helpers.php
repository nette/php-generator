<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * PHP code generator utils.
 *
 * @author     David Grudl
 */
class Helpers
{
	const PHP_IDENT = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';


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

		} elseif (is_array($var)) {
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === NULL) {
				$marker = uniqid("\x00", TRUE);
			}
			if (empty($var)) {
				$out = '';

			} elseif ($level > 50 || isset($var[$marker])) {
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

		} elseif (is_object($var)) {
			$arr = (array) $var;
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {
				$out = '';

			} elseif ($level > 50 || in_array($var, $list, TRUE)) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$out = "\n";
				$list[] = $var;
				foreach ($arr as $k => & $v) {
					if ($k[0] === "\x00") {
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$out .= "$space\t" . self::_dump($k, $level + 1) . " => " . self::_dump($v, $level + 1) . ",\n";
				}
				array_pop($list);
				$out .= $space;
			}
			return get_class($var) === 'stdClass'
				? "(object) array($out)"
				: __CLASS__ . "::createObject('" . get_class($var) . "', array($out))";

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
				$arg = implode(', ', array_map(array(__CLASS__, 'dump'), $arg));
				$statement = substr_replace($statement, $arg, $a, 2);

			} else {
				$arg = substr($statement, $a - 1, 1) === '$' || in_array(substr($statement, $a - 2, 2), array('->', '::'))
					? self::formatMember($arg) : self::_dump($arg);
				$statement = substr_replace($statement, $arg, $a, 1);
			}
			$a = strpos($statement, '?', $a + strlen($arg));
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
			: $name ;
	}


	/**
	 * @return bool
	 */
	public static function isIdentifier($value)
	{
		return is_string($value) && preg_match('#^' . self::PHP_IDENT . '\z#', $value);
	}


	public static function createObject($class, array $props)
	{
		return unserialize('O' . substr(serialize((string) $class), 1, -1) . substr(serialize($props), 1));
	}

}
