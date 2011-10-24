<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Utils\PhpGenerator;

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



	/**
	 * Returns a PHP representation of a object member.
	 * @return string
	 */
	public static function dumpMember($var)
	{
		return preg_match('#^' . self::PHP_IDENT . '$#', $var) ? $var : '{' . self::_dump($var) . '}';
	}



	private static function _dump(&$var, $level = 0)
	{
		if ($var instanceof PhpLiteral) {
			return $var->value;

		} elseif (is_float($var)) {
			$var = var_export($var, TRUE);
			return strpos($var, '.') === FALSE ? $var . '.0' : $var;

		} elseif (is_bool($var)) {
			return $var ? 'TRUE' : 'FALSE';

		} elseif (is_string($var) && (preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || preg_last_error())) {
			static $table;
			if ($table === NULL) {
				foreach (range("\x00", "\xFF") as $ch) {
					$table[$ch] = ord($ch) < 32 || ord($ch) >= 127
						? '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT)
						: $ch;
				}
				$table["\r"] = '\r';
				$table["\n"] = '\n';
				$table["\t"] = '\t';
				$table['$'] = '\\$';
				$table['\\'] = '\\\\';
				$table['"'] = '\\"';
			}
			return '"' . strtr($var, $table) . '"';

		} elseif (is_array($var)) {
			$s = '';
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === NULL) {
				$marker = uniqid("\x00", TRUE);
			}
			if (empty($var)) {

			} elseif ($level > 50 || isset($var[$marker])) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$s .= "\n";
				$var[$marker] = TRUE;
				$counter = 0;
				foreach ($var as $k => &$v) {
					if ($k !== $marker) {
						$s .= "$space\t" . ($k === $counter ? '' : self::_dump($k) . " => ") . self::_dump($v, $level + 1) . ",\n";
						$counter = is_int($k) ? $k + 1 : $counter;
					}
				}
				unset($var[$marker]);
				$s .= $space;
			}
			return "array($s)";

		} elseif (is_object($var)) {
			$arr = (array) $var;
			$s = '';
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {

			} elseif ($level > 50 || in_array($var, $list, TRUE)) {
				throw new Nette\InvalidArgumentException('Nesting level too deep or recursive dependency.');

			} else {
				$s .= "\n";
				$list[] = $var;
				foreach ($arr as $k => &$v) {
					if ($k[0] === "\x00") {
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$s .= "$space\t" . self::_dump($k) . " => " . self::_dump($v, $level + 1) . ",\n";
				}
				array_pop($list);
				$s .= $space;
			}
			return get_class($var) === 'stdClass'
				? "(object) array($s)"
				: get_class($var) . "::__set_state(array($s))";

		} else {
			return var_export($var, TRUE);
		}
	}



	/** @return string */
	public static function generate($statement)
	{
		$args = func_get_args();
		unset($args[0]);
		foreach ($args as $key => $arg) {
			$args[$key] = self::_dump($arg);
		}
		if (strpos($statement, '?') === FALSE) {
			return $statement .= '(' . implode(', ', $args) . ');';
		}

		$a = strpos($statement, '?');
		$i = 1;
		while ($a !== FALSE) {
			$statement = substr_replace($statement, $args[$i], $a, 1);
			$a = strpos($statement, '?', $a + strlen($args[$i]));
			$i++;
		}
		return $statement . ';';
	}

}
