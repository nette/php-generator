<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;


/**
 * Global function.
 */
final class GlobalFunction
{
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	public static function from(string $function, bool $withBody = false): self
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($function), $withBody);
	}


	public function __toString(): string
	{
		return (new Printer)->printFunction($this);
	}
}
