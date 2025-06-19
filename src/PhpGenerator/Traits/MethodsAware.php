<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\Method;
use function strtolower;


/**
 * @internal
 */
trait MethodsAware
{
	/** @var array<string, Method> */
	private array $methods = [];


	/**
	 * Replaces all methods.
	 * @param  Method[]  $methods
	 */
	public function setMethods(array $methods): static
	{
		(function (Method ...$methods) {})(...$methods);
		$this->methods = [];
		foreach ($methods as $m) {
			$this->methods[strtolower($m->getName())] = $m;
		}

		return $this;
	}


	/** @return Method[] */
	public function getMethods(): array
	{
		$res = [];
		foreach ($this->methods as $m) {
			$res[$m->getName()] = $m;
		}

		return $res;
	}


	public function getMethod(string $name): Method
	{
		return $this->methods[strtolower($name)] ?? throw new Nette\InvalidArgumentException("Method '$name' not found.");
	}


	/**
	 * Adds a method. If it already exists, throws an exception or overwrites it if $overwrite is true.
	 */
	public function addMethod(string $name, bool $overwrite = false): Method
	{
		$lower = strtolower($name);
		if (!$overwrite && isset($this->methods[$lower])) {
			throw new Nette\InvalidStateException("Cannot add method '$name', because it already exists.");
		}
		$method = new Method($name);
		if (!$this->isInterface()) {
			$method->setPublic();
		}

		return $this->methods[$lower] = $method;
	}


	public function removeMethod(string $name): static
	{
		unset($this->methods[strtolower($name)]);
		return $this;
	}


	public function hasMethod(string $name): bool
	{
		return isset($this->methods[strtolower($name)]);
	}
}
