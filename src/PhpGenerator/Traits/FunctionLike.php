<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;


/**
 * @internal
 */
trait FunctionLike
{
	/** @var string */
	private $body = '';

	/** @var array of name => Parameter */
	private $parameters = [];

	/** @var bool */
	private $variadic = FALSE;

	/** @var string|NULL */
	private $returnType;

	/** @var bool */
	private $returnReference = FALSE;

	/** @var bool */
	private $returnNullable = FALSE;

	/** @var PhpNamespace|NULL */
	private $namespace;


	/**
	 * @param  string
	 * @return static
	 */
	public function setBody($code, array $args = NULL)
	{
		$this->body = $args === NULL ? $code : Helpers::formatArgs($code, $args);
		return $this;
	}


	/**
	 * @return string
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
	 * @param  Parameter[]
	 * @return static
	 */
	public function setParameters(array $val)
	{
		$this->parameters = [];
		foreach ($val as $v) {
			if (!$v instanceof Parameter) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Parameter[].');
			}
			$this->parameters[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Parameter[]
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * @param  string  without $
	 * @return Parameter
	 */
	public function addParameter($name, $defaultValue = NULL)
	{
		$param = new Parameter($name);
		if (func_num_args() > 1) {
			$param->setOptional(TRUE)->setDefaultValue($defaultValue);
		}
		return $this->parameters[$name] = $param;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setVariadic($state = TRUE)
	{
		$this->variadic = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isVariadic()
	{
		return $this->variadic;
	}


	/**
	 * @param  string|NULL
	 * @return static
	 */
	public function setReturnType($val)
	{
		$this->returnType = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getReturnType()
	{
		return $this->returnType;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReturnReference($state = TRUE)
	{
		$this->returnReference = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getReturnReference()
	{
		return $this->returnReference;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReturnNullable($state = TRUE)
	{
		$this->returnNullable = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getReturnNullable()
	{
		return $this->returnNullable;
	}


	/**
	 * @return static
	 */
	public function setNamespace(PhpNamespace $val = NULL)
	{
		$this->namespace = $val;
		return $this;
	}


	/**
	 * @return string
	 */
	protected function parametersToString()
	{
		$params = [];
		foreach ($this->parameters as $param) {
			$variadic = $this->variadic && $param === end($this->parameters);
			$hint = $param->getTypeHint();
			$params[] = ($hint ? ($param->isNullable() ? '?' : '') . ($this->namespace ? $this->namespace->unresolveName($hint) : $hint) . ' ' : '')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . Helpers::dump($param->defaultValue) : '');
		}
		return '(' . implode(', ', $params) . ')';
	}


	/**
	 * @return string
	 */
	protected function returnTypeToString()
	{
		return $this->returnType
			? ': ' . ($this->returnNullable ? '?' : '') . ($this->namespace ? $this->namespace->unresolveName($this->returnType) : $this->returnType)
			: '';
	}

}
