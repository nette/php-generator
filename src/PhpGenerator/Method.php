<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Method or function description.
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

	/** @var Parameter[] */
	private $uses = [];

	/** @var string|FALSE */
	private $body = '';

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
		return (new Factory)->fromFunctionReflection(
			$method instanceof \ReflectionFunctionAbstract ? $method : Nette\Utils\Callback::toReflection($method)
		);
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$uses = [];
		foreach ($this->uses as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}
		return Helpers::formatDocComment($this->comment . "\n")
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->visibility ? $this->visibility . ' ' : '')
			. ($this->static ? 'static ' : '')
			. 'function '
			. ($this->returnReference ? '&' : '')
			. $this->name
			. $this->parametersToString()
			. ($this->uses ? ' use (' . implode(', ', $uses) . ')' : '')
			. $this->returnTypeToString()
			. ($this->abstract || $this->body === FALSE ? ';'
				: ($this->name ? "\n" : ' ') . "{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}');
	}


	/**
	 * @return static
	 */
	public function setUses(array $val)
	{
		$this->uses = $val;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getUses()
	{
		return $this->uses;
	}


	/**
	 * @return Parameter
	 */
	public function addUse($name)
	{
		return $this->uses[] = new Parameter($name);
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
	 * @param  string
	 * @return static
	 */
	public function addBody($code, array $args = NULL)
	{
		$this->body .= ($args === NULL ? $code : Helpers::formatArgs($code, $args)) . "\n";
		return $this;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setStatic($val)
	{
		$this->static = (bool) $val;
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
	public function setFinal($val)
	{
		$this->final = (bool) $val;
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
	public function setAbstract($val)
	{
		$this->abstract = (bool) $val;
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
