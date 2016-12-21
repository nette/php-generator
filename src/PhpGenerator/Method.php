<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

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
		if ($method instanceof \ReflectionMethod) {
			trigger_error(__METHOD__ . '() accepts only method name.', E_USER_DEPRECATED);
		} else {
			$method = Nette\Utils\Callback::toReflection($method);
		}
		return (new Factory)->fromMethodReflection($method);
	}


	/**
	 * @param  string
	 */
	public function __construct($name)
	{
		if (!Helpers::isIdentifier($name)) {
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
