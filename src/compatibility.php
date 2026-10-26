<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

if (false) {
	/** alias for Nette\PhpGenerator\PropertyAccessMode */
	enum PropertyHookType: string
	{
		case Set = 'set';
		case Get = 'get';
	}
} elseif (!enum_exists(PropertyHookType::class)) {
	class_alias(PropertyAccessMode::class, PropertyHookType::class);
}
