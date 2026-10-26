<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Constants and helpers for PHP type declarations.
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


	/**
	 * Adds or removes the nullable modifier from a type. For union types, appends or removes |null.
	 */
	public static function nullable(string $type, bool $nullable = true): string
	{
		return (string) Helpers::nullableType(Nette\Utils\Type::fromString($type), $nullable);
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
