<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class method.
 *
 * @property string|FALSE $body
 */
class Method
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;

	/** @var bool */
	private $static = FALSE;

	/** @var bool */
	private $final = FALSE;

	/** @var bool */
	private $abstract = FALSE;


	/**
	 * @param  callable
	 * @return static
	 */
	public static function from($method)
	{
		$method = $method instanceof \ReflectionFunctionAbstract ? $method : Nette\Utils\Callback::toReflection($method);
		if ($method instanceof \ReflectionFunction) {
			trigger_error('For global functions or closures use Nette\PhpGenerator\GlobalFunction or Nette\PhpGenerator\Closure.', E_USER_DEPRECATED);
			return (new Factory)->fromFunctionReflection($method);
		}
		return (new Factory)->fromMethodReflection($method);
	}


	/**
	 * @param  string
	 */
	public function __construct($name)
	{
		if ($name === NULL) {
			throw new Nette\DeprecatedException('For closures use Nette\PhpGenerator\Closure instead of Nette\PhpGenerator\Method.');
		} elseif (!Helpers::isIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid name.");
		}
		$this->name = $name;
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		return Helpers::formatDocComment($this->comment . "\n")
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->visibility ? $this->visibility . ' ' : '')
			. ($this->static ? 'static ' : '')
			. 'function '
			. ($this->returnReference ? '&' : '')
			. $this->name
			. $this->parametersToString()
			. $this->returnTypeToString()
			. ($this->abstract || $this->body === FALSE
				? ';'
				: "\n{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}');
	}


	/**
	 * @param  string|FALSE
	 * @return static
	 */
	public function setBody($code, array $args = NULL)
	{
		$this->body = $args === NULL ? $code : Helpers::formatArgs($code, $args);
		return $this;
	}


	/**
	 * @return string|FALSE
	 */
	public function getBody()
	{
		return $this->body;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setStatic($state = TRUE)
	{
		$this->static = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->static;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setFinal($state = TRUE)
	{
		$this->final = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->final;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setAbstract($state = TRUE)
	{
		$this->abstract = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return $this->abstract;
	}

}
