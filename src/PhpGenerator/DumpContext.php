<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;


/**
 * Location where a dumped value will appear in generated code.
 */
enum DumpContext
{
	case Expression;
	case Constant;
	case Property;
	case Parameter;
	case Attribute;
}
