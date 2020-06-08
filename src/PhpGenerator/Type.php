<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;


/**
 * PHP return, property and parameter types.
 */
class Type
{
	public const
		STRING = 'string',
		INT = 'int',
		FLOAT = 'float',
		BOOL = 'bool',
		ARRAY = 'array',
		OBJECT = 'object',
		CALLABLE = 'callable',
		ITERABLE = 'iterable',
		VOID = 'void',
		MIXED = 'mixed',
		FALSE = 'false',
		NULL = 'null',
		SELF = 'self',
		PARENT = 'parent',
		STATIC = 'static';


	public static function nullable(string $type, bool $state = true): string
	{
		return ($state ? '?' : '') . ltrim($type, '?');
	}


	public static function union(string ...$types): string
	{
		return implode('|', $types);
	}


	public static function getType($value): ?string
	{
		if (is_object($value)) {
			return get_class($value);
		} elseif (is_int($value)) {
			return self::INT;
		} elseif (is_float($value)) {
			return self::FLOAT;
		} elseif (is_string($value)) {
			return self::STRING;
		} elseif (is_bool($value)) {
			return self::BOOL;
		} elseif (is_array($value)) {
			return self::ARRAY;
		} else {
			return null;
		}
	}
}
