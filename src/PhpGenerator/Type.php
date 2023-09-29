<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


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

	/** @deprecated use Type::String */
	public const STRING = self::String;

	/** @deprecated use Type::Int */
	public const INT = self::Int;

	/** @deprecated use Type::Float */
	public const FLOAT = self::Float;

	/** @deprecated use Type::Bool */
	public const BOOL = self::Bool;

	/** @deprecated use Type::Array */
	public const ARRAY = self::Array;

	/** @deprecated use Type::Object */
	public const OBJECT = self::Object;

	/** @deprecated use Type::Callable */
	public const CALLABLE = self::Callable;

	/** @deprecated use Type::Iterable */
	public const ITERABLE = self::Iterable;

	/** @deprecated use Type::Void */
	public const VOID = self::Void;

	/** @deprecated use Type::Never */
	public const NEVER = self::Never;

	/** @deprecated use Type::Mixed */
	public const MIXED = self::Mixed;

	/** @deprecated use Type::False */
	public const FALSE = self::False;

	/** @deprecated use Type::Null */
	public const NULL = self::Null;

	/** @deprecated use Type::Self */
	public const SELF = self::Self;

	/** @deprecated use Type::Parent */
	public const PARENT = self::Parent;

	/** @deprecated use Type::Static */
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
