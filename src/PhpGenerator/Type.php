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
		String = 'string',
		Int = 'int',
		Float = 'float',
		Bool = 'bool',
		Array = 'array',
		Object = 'object',
		Callable = 'callable',
		Iterable = 'iterable',
		Void = 'void',
		Never = 'never',
		Mixed = 'mixed',
		False = 'false',
		Null = 'null',
		Self = 'self',
		Parent = 'parent',
		Static = 'static';

	/** @deprecated */
	public const
		STRING = self::String,
		INT = self::Int,
		FLOAT = self::Float,
		BOOL = self::Bool,
		ARRAY = self::Array,
		OBJECT = self::Object,
		CALLABLE = self::Callable,
		ITERABLE = self::Iterable,
		VOID = self::Void,
		NEVER = self::Never,
		MIXED = self::Mixed,
		FALSE = self::False,
		NULL = self::Null,
		SELF = self::Self,
		PARENT = self::Parent,
		STATIC = self::Static;


	public static function nullable(string $type, bool $state = true): string
	{
		return ($state ? '?' : '') . ltrim($type, '?');
	}


	public static function union(string ...$types): string
	{
		return implode('|', $types);
	}


	public static function intersection(string ...$types): string
	{
		return implode('&', $types);
	}


	/** @deprecated  use get_debug_type() */
	public static function getType(mixed $value): ?string
	{
		trigger_error(__METHOD__ . '() is deprecated, use PHP function get_debug_type()', E_USER_DEPRECATED);
		return is_resource($value) ? null : get_debug_type($value);
	}
}
