<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


final class ClassManipulator
{
	public function __construct(
		private ClassType $class,
	) {
	}


	/**
	 * Inherits property from parent class.
	 */
	public function inheritProperty(string $name, bool $returnIfExists = false): Property
	{
		$extends = $this->class->getExtends();
		if ($this->class->hasProperty($name)) {
			return $returnIfExists
				? $this->class->getProperty($name)
				: throw new Nette\InvalidStateException("Cannot inherit property '$name', because it already exists.");

		} elseif (!$extends) {
			throw new Nette\InvalidStateException("Class '{$this->class->getName()}' has not setExtends() set.");
		}

		try {
			$rp = new \ReflectionProperty($extends, $name);
		} catch (\ReflectionException) {
			throw new Nette\InvalidStateException("Property '$name' has not been found in ancestor {$extends}");
		}

		$property = (new Factory)->fromPropertyReflection($rp);
		$this->class->addMember($property);
		return $property;
	}


	/**
	 * Inherits method from parent class or interface.
	 */
	public function inheritMethod(string $name, bool $returnIfExists = false): Method
	{
		$parents = [...(array) $this->class->getExtends(), ...$this->class->getImplements()];
		if ($this->class->hasMethod($name)) {
			return $returnIfExists
				? $this->class->getMethod($name)
				: throw new Nette\InvalidStateException("Cannot inherit method '$name', because it already exists.");

		} elseif (!$parents) {
			throw new Nette\InvalidStateException("Class '{$this->class->getName()}' has neither setExtends() nor setImplements() set.");
		}

		foreach ($parents as $parent) {
			try {
				$rm = new \ReflectionMethod($parent, $name);
			} catch (\ReflectionException) {
				continue;
			}
			return $this->implementMethod($rm);
		}

		throw new Nette\InvalidStateException("Method '$name' has not been found in any ancestor: " . implode(', ', $parents));
	}


	/**
	 * Implements all methods from the given interface or abstract class.
	 */
	public function implement(string $name): void
	{
		$definition = new \ReflectionClass($name);
		if ($definition->isInterface()) {
			$this->class->addImplement($name);
		} elseif ($definition->isAbstract()) {
			$this->class->setExtends($name);
		} else {
			throw new Nette\InvalidArgumentException("'$name' is not an interface or abstract class.");
		}

		foreach ($definition->getMethods() as $method) {
			if (!$this->class->hasMethod($method->getName()) && $method->isAbstract()) {
				$this->implementMethod($method);
			}
		}
	}


	private function implementMethod(\ReflectionMethod $rm): Method
	{
		$method = (new Factory)->fromMethodReflection($rm);
		$method->setAbstract(false);
		$this->class->addMember($method);
		return $method;
	}


	/** @deprecated use implement() */
	public function implementInterface(string $interfaceName): void
	{
		trigger_error(__METHOD__ . '() is deprecated, use implement()', E_USER_DEPRECATED);
		$this->implement($interfaceName);
	}
}
