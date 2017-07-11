<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Global function.
 *
 * @property string $body
 */
class GlobalFunction
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\CommentAware;

	/**
	 * @param  string
	 * @return static
	 */
	public static function from($function)
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($function));
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		return Helpers::formatDocComment($this->comment . "\n")
			. 'function '
			. ($this->returnReference ? '&' : '')
			. $this->name
			. $this->parametersToString()
			. $this->returnTypeToString()
			. "\n{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}';
	}
}
