<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;


/**
 * Context in which a dumped value will be used.
 */
enum DumpContext
{
	case Expression;
	case Constant;
	case Property;
	case Parameter;
	case Attribute;
}
