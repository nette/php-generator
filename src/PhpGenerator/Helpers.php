<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * @internal
 */
final class Helpers
{
	use Nette\StaticClass;

	public const PHP_IDENT = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';


	/** @deprecated  use Nette\PhpGenerator\Dumper::dump() */
	public static function dump($var): string
	{
		return (new Dumper)->dump($var);
	}


	/** @deprecated  use Nette\PhpGenerator\Dumper::format() */
	public static function format(string $statement, ...$args): string
	{
		return (new Dumper)->format($statement, ...$args);
	}


	/** @deprecated  use Nette\PhpGenerator\Dumper::format() */
	public static function formatArgs(string $statement, array $args): string
	{
		return (new Dumper)->format($statement, ...$args);
	}


	public static function formatDocComment(string $content): string
	{
		if (($s = trim($content)) === '') {
			return '';
		} elseif (strpos($content, "\n") === false) {
			return "/** $s */\n";
		} else {
			return str_replace("\n", "\n * ", "/**\n$s") . "\n */\n";
		}
	}


	public static function unformatDocComment(string $comment): string
	{
		return preg_replace('#^\s*\* ?#m', '', trim(trim(trim($comment), '/*')));
	}


	public static function indentPhp(string $s, int $level = 1, string $chars = "\t"): string
	{
		$tbl = [];
		$s = str_replace("\r\n", "\n", $s);

		if ($level && strpos($s, "\n") !== false && preg_match('#\?>|<<<|"|\'#', $s)) {
			static $save = [T_CONSTANT_ENCAPSED_STRING => 1, T_ENCAPSED_AND_WHITESPACE => 1, T_INLINE_HTML => 1, T_START_HEREDOC => 1, T_CLOSE_TAG => 1];
			$tokens = token_get_all("<?php\n" . $s);
			unset($tokens[0]);
			$s = '';
			foreach ($tokens as $token) {
				if (isset($save[$token[0]]) && strpos($token[1], "\n") !== false) {
					$s .= $id = "\00" . count($tbl) . "\00";
					$tbl[$id] = $token[1];
				} else {
					$s .= is_array($token) ? $token[1] : $token;
				}
			}
		}

		if ($level > 0) {
			$s = Nette\Utils\Strings::indent($s, $level, $chars);
		} elseif ($level < 0) {
			$s = preg_replace('#^(\t|\ \ \ \ ){1,' . (-$level) . '}#m', '', $s);
		}
		return strtr($s, $tbl);
	}


	public static function isIdentifier($value): bool
	{
		return is_string($value) && preg_match('#^' . self::PHP_IDENT . '$#D', $value);
	}


	public static function isNamespaceIdentifier($value, bool $allowLeadingSlash = false): bool
	{
		$re = '#^' . ($allowLeadingSlash ? '\\\\?' : '') . self::PHP_IDENT . '(\\\\' . self::PHP_IDENT . ')*$#D';
		return is_string($value) && preg_match($re, $value);
	}


	public static function extractNamespace(string $name): string
	{
		return ($pos = strrpos($name, '\\')) ? substr($name, 0, $pos) : '';
	}


	public static function extractShortName(string $name): string
	{
		return ($pos = strrpos($name, '\\')) === false ? $name : substr($name, $pos + 1);
	}


	public static function tabsToSpaces(string $s, int $count = 4): string
	{
		return str_replace("\t", str_repeat(' ', $count), $s);
	}


	/** @internal */
	public static function createObject(string $class, array $props)
	{
		return Dumper::createObject($class, $props);
	}
}
