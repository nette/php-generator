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
 * @property string $body
 */
class Method extends Member
{
	/** @var array of name => Parameter */
	private $parameters = [];

	/** @var array of name => bool */
	private $uses = [];

	/** @var string|FALSE */
	private $body = '';

	/** @var bool */
	private $static = FALSE;

	/** @var bool */
	private $final = FALSE;

	/** @var bool */
	private $abstract = FALSE;

	/** @var bool */
	private $returnReference = FALSE;

	/** @var bool */
	private $variadic = FALSE;

	/** @var PhpNamespace|NULL */
	private $namespace;

	/** @var string|NULL */
	private $returnType;

	/** @var bool */
	private $returnNullable;


	/**
	 * @return static
	 */
	public static function from($from)
	{
		return (new Factory)->fromFunctionReflection(
			$from instanceof \ReflectionFunctionAbstract ? $from : Nette\Utils\Callback::toReflection($from)
		);
	}


	/**
	 * @param  string|NULL
	 */
	public function __construct($name = NULL)
	{
		$this->setName($name);
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$parameters = [];
		foreach ($this->parameters as $param) {
			$variadic = $this->variadic && $param === end($this->parameters);
			$hint = $param->getTypeHint();
			$parameters[] = ($hint ? ($param->isNullable() ? '?' : '') . ($this->namespace ? $this->namespace->unresolveName($hint) : $hint) . ' ' : '')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . Helpers::dump($param->defaultValue) : '');
		}
		$uses = [];
		foreach ($this->uses as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}

		return Helpers::formatDocComment($this->getComment() . "\n")
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->getVisibility() ? $this->getVisibility() . ' ' : '')
			. ($this->static ? 'static ' : '')
			. 'function '
			. ($this->returnReference ? '&' : '')
			. $this->getName()
			. '(' . implode(', ', $parameters) . ')'
			. ($this->uses ? ' use (' . implode(', ', $uses) . ')' : '')
			. ($this->returnType ? ': ' . ($this->returnNullable ? '?' : '')
				. ($this->namespace ? $this->namespace->unresolveName($this->returnType) : $this->returnType) : '')
			. ($this->abstract || $this->body === FALSE ? ';'
				: ($this->getName() ? "\n" : ' ') . "{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}');
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


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReturnReference($val)
	{
		$this->returnReference = (bool) $val;
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
	public function setReturnNullable($val)
	{
		$this->returnNullable = (bool) $val;
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
	 * @param  bool
	 * @return static
	 */
	public function setVariadic($val)
	{
		$this->variadic = (bool) $val;
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
	 * @return static
	 */
	public function setNamespace(PhpNamespace $val = NULL)
	{
		$this->namespace = $val;
		return $this;
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

}
