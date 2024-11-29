<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;


/**
 * Member visibility.
 */
enum Visibility: string
{
	case Public = 'public';
	case Protected = 'protected';
	case Private = 'private';
}
