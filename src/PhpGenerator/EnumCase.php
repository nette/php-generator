<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Enum case.
 */
final class EnumCase
{
	use Nette\SmartObject;
	use Traits\NameAware;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	private mixed $value = null;


	public function setValue($val): static
	{
		$this->value = $val;
		return $this;
	}


	public function getValue()
	{
		return $this->value;
	}
}
