<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

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
	private $variadic = false;

	/** @var string|null */
	private $returnType;

	/** @var bool */
	private $returnReference = false;

	/** @var bool */
	private $returnNullable = false;

	/** @var PhpNamespace|null */
	private $namespace;
	
	/** @var string */
	private $implode = ', ';

	/**
	 * @return static
	 */
	public function setImplode(string $glue): self
	{
		$this->implode = $glue;
		return $this;
	}
	
	/**
	 * @return static
	 */
	public function setBody(string $code, array $args = null): self
	{
		$this->body = $args === null ? $code : Helpers::formatArgs($code, $args);
		return $this;
	}


	public function getBody(): string
	{
		return $this->body;
	}


	/**
	 * @return static
	 */
	public function addBody(string $code, array $args = null): self
	{
		$this->body .= ($args === null ? $code : Helpers::formatArgs($code, $args)) . "\n";
		return $this;
	}


	/**
	 * @param  Parameter[]  $val
	 * @return static
	 */
	public function setParameters(array $val): self
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
	public function getParameters(): array
	{
		return $this->parameters;
	}


	/**
	 * @param  string  $name without $
	 */
	public function addParameter(string $name, $defaultValue = null): Parameter
	{
		$param = new Parameter($name);
		if (func_num_args() > 1) {
			$param->setDefaultValue($defaultValue);
		}
		return $this->parameters[$name] = $param;
	}


	/**
	 * @return static
	 */
	public function setVariadic(bool $state = true): self
	{
		$this->variadic = $state;
		return $this;
	}


	public function isVariadic(): bool
	{
		return $this->variadic;
	}


	/**
	 * @return static
	 */
	public function setReturnType(?string $val): self
	{
		$this->returnType = $val;
		return $this;
	}


	public function getReturnType(): ?string
	{
		return $this->returnType;
	}


	/**
	 * @return static
	 */
	public function setReturnReference(bool $state = true): self
	{
		$this->returnReference = $state;
		return $this;
	}


	public function getReturnReference(): bool
	{
		return $this->returnReference;
	}


	/**
	 * @return static
	 */
	public function setReturnNullable(bool $state = true): self
	{
		$this->returnNullable = $state;
		return $this;
	}


	public function getReturnNullable(): bool
	{
		return $this->returnNullable;
	}


	/**
	 * @return static
	 */
	public function setNamespace(PhpNamespace $val = null): self
	{
		$this->namespace = $val;
		return $this;
	}


	protected function parametersToString(): string
	{
		$params = [];
		foreach ($this->parameters as $param) {
			$variadic = $this->variadic && $param === end($this->parameters);
			$hint = $param->getTypeHint();
			$params[] = ($hint ? ($param->isNullable() ? '?' : '') . ($this->namespace ? $this->namespace->unresolveName($hint) : $hint) . ' ' : '')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . Helpers::dump($param->getDefaultValue()) : '');
		}
		return '(' . implode($this->implode, $params) . ')';
	}


	protected function returnTypeToString(): string
	{
		return $this->returnType
			? ': ' . ($this->returnNullable ? '?' : '') . ($this->namespace ? $this->namespace->unresolveName($this->returnType) : $this->returnType)
			: '';
	}
}
