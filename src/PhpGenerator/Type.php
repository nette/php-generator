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
		NULL = 'null',
		SELF = 'self',
		PARENT = 'parent';


	public static function nullable(string $type, bool $state = true): string
	{
		return ($state ? '?' : '') . ltrim($type, '?');
	}


	public static function union(string ...$types): string
	{
		return implode('|', $types);
	}
}
