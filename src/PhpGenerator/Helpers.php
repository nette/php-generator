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

	public const KEYWORDS = [
		// built-in types
		'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1, 'object' => 1,
		'callable' => 1, 'iterable' => 1, 'void' => 1, 'null' => 1, 'mixed' => 1, 'false' => 1,
		'never' => 1,

		// class keywords
		'self' => 1, 'parent' => 1, 'static' => 1,
	];


	/** @deprecated  use (new Nette\PhpGenerator\Dumper)->dump() */
	public static function dump($var): string
	{
		return (new Dumper)->dump($var);
	}


	/** @deprecated  use (new Nette\PhpGenerator\Dumper)->format() */
	public static function format(string $statement, ...$args): string
	{
		return (new Dumper)->format($statement, ...$args);
	}


	/** @deprecated  use (new Nette\PhpGenerator\Dumper)->format() */
	public static function formatArgs(string $statement, array $args): string
	{
		return (new Dumper)->format($statement, ...$args);
	}


	public static function formatDocComment(string $content): string
	{
		$s = trim($content);
		$s = str_replace('*/', '* /', $s);
		if ($s === '') {
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


	public static function unindent(string $s, int $level = 1): string
	{
		return preg_replace('#^(\t|\ \ \ \ ){1,' . $level . '}#m', '', $s);
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
		return ($pos = strrpos($name, '\\')) === false
			? $name
			: substr($name, $pos + 1);
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
