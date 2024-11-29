<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use function implode, preg_match, preg_replace, str_contains;


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
		True = 'true',
		False = 'false',
		Null = 'null',
		Self = 'self',
		Parent = 'parent',
		Static = 'static';

	#[\Deprecated('use Type::String')]
	public const STRING = self::String;

	#[\Deprecated('use Type::Int')]
	public const INT = self::Int;

	#[\Deprecated('use Type::Float')]
	public const FLOAT = self::Float;

	#[\Deprecated('use Type::Bool')]
	public const BOOL = self::Bool;

	#[\Deprecated('use Type::Array')]
	public const ARRAY = self::Array;

	#[\Deprecated('use Type::Object')]
	public const OBJECT = self::Object;

	#[\Deprecated('use Type::Callable')]
	public const CALLABLE = self::Callable;

	#[\Deprecated('use Type::Iterable')]
	public const ITERABLE = self::Iterable;

	#[\Deprecated('use Type::Void')]
	public const VOID = self::Void;

	#[\Deprecated('use Type::Never')]
	public const NEVER = self::Never;

	#[\Deprecated('use Type::Mixed')]
	public const MIXED = self::Mixed;

	#[\Deprecated('use Type::False')]
	public const FALSE = self::False;

	#[\Deprecated('use Type::Null')]
	public const NULL = self::Null;

	#[\Deprecated('use Type::Self')]
	public const SELF = self::Self;

	#[\Deprecated('use Type::Parent')]
	public const PARENT = self::Parent;

	#[\Deprecated('use Type::Static')]
	public const STATIC = self::Static;


	public static function nullable(string $type, bool $nullable = true): string
	{
		if (str_contains($type, '&')) {
			return $nullable
				? throw new Nette\InvalidArgumentException('Intersection types cannot be nullable.')
				: $type;
		}

		$nnType = preg_replace('#^\?|^null\||\|null(?=\||$)#i', '', $type);
		$always = (bool) preg_match('#^(null|mixed)$#i', $nnType);
		if ($nullable) {
			return match (true) {
				$always, $type !== $nnType => $type,
				str_contains($type, '|') => $type . '|null',
				default => '?' . $type,
			};
		} else {
			return $always
				? throw new Nette\InvalidArgumentException("Type $type cannot be not nullable.")
				: $nnType;
		}
	}


	public static function union(string ...$types): string
	{
		return implode('|', $types);
	}


	public static function intersection(string ...$types): string
	{
		return implode('&', $types);
	}
}
