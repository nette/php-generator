<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class/Interface/Trait description.
 *
 * @property Method[] $methods
 * @property Property[] $properties
 */
final class ClassType
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	public const
		TYPE_CLASS = 'class',
		TYPE_INTERFACE = 'interface',
		TYPE_TRAIT = 'trait';

	public const
		VISIBILITY_PUBLIC = 'public',
		VISIBILITY_PROTECTED = 'protected',
		VISIBILITY_PRIVATE = 'private';

	/** @var PhpNamespace|null */
	private $namespace;

	/** @var string|null */
	private $name;

	/** @var string  class|interface|trait */
	private $type = self::TYPE_CLASS;

	/** @var bool */
	private $final = false;

	/** @var bool */
	private $abstract = false;

	/** @var string|string[] */
	private $extends = [];

	/** @var string[] */
	private $implements = [];

	/** @var array[] */
	private $traits = [];

	/** @var Constant[] name => Constant */
	private $consts = [];

	/** @var Property[] name => Property */
	private $properties = [];

	/** @var Method[] name => Method */
	private $methods = [];


	/**
	 * @param  string|object  $class
	 * @return static
	 */
	public static function from($class): self
	{
		return (new Factory)->fromClassReflection(new \ReflectionClass($class));
	}


	public function __construct(string $name = null, PhpNamespace $namespace = null)
	{
		$this->setName($name);
		$this->namespace = $namespace;
	}


	public function __toString(): string
	{
		try {
			return (new Printer)->printClass($this, $this->namespace);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}


	/**
	 * Deprecated: an object can be in multiple namespaces.
	 * @deprecated
	 */
	public function getNamespace(): ?PhpNamespace
	{
		return $this->namespace;
	}


	/**
	 * @return static
	 */
	public function setName(?string $name): self
	{
		if ($name !== null && !Helpers::isIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
		}
		$this->name = $name;
		return $this;
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	/**
	 * @return static
	 */
	public function setType(string $type): self
	{
		if (!in_array($type, [self::TYPE_CLASS, self::TYPE_INTERFACE, self::TYPE_TRAIT], true)) {
			throw new Nette\InvalidArgumentException('Argument must be class|interface|trait.');
		}
		$this->type = $type;
		return $this;
	}


	public function getType(): string
	{
		return $this->type;
	}


	/**
	 * @return static
	 */
	public function setFinal(bool $state = true): self
	{
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	/**
	 * @return static
	 */
	public function setAbstract(bool $state = true): self
	{
		$this->abstract = $state;
		return $this;
	}


	public function isAbstract(): bool
	{
		return $this->abstract;
	}


	/**
	 * @param  string|string[]  $names
	 * @return static
	 */
	public function setExtends($names): self
	{
		if (!is_string($names) && !is_array($names)) {
			throw new Nette\InvalidArgumentException('Argument must be string or string[].');
		}
		$this->validateNames((array) $names);
		$this->extends = $names;
		return $this;
	}


	/**
	 * @return string|string[]
	 */
	public function getExtends()
	{
		return $this->extends;
	}


	/**
	 * @return static
	 */
	public function addExtend(string $name): self
	{
		$this->validateNames([$name]);
		$this->extends = (array) $this->extends;
		$this->extends[] = $name;
		return $this;
	}


	/**
	 * @param  string[]  $names
	 * @return static
	 */
	public function setImplements(array $names): self
	{
		$this->validateNames($names);
		$this->implements = $names;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getImplements(): array
	{
		return $this->implements;
	}


	/**
	 * @return static
	 */
	public function addImplement(string $name): self
	{
		$this->validateNames([$name]);
		$this->implements[] = $name;
		return $this;
	}


	/**
	 * @param  string[]  $names
	 * @return static
	 */
	public function setTraits(array $names): self
	{
		$this->validateNames($names);
		$this->traits = array_fill_keys($names, []);
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getTraits(): array
	{
		return array_keys($this->traits);
	}


	/**
	 * @internal
	 */
	public function getTraitResolutions(): array
	{
		return $this->traits;
	}


	/**
	 * @return static
	 */
	public function addTrait(string $name, array $resolutions = []): self
	{
		$this->validateNames([$name]);
		$this->traits[$name] = $resolutions;
		return $this;
	}


	/**
	 * @param  Method|Property|Constant  $member
	 * @return static
	 */
	public function addMember($member): self
	{
		if ($member instanceof Method) {
			if ($this->type === self::TYPE_INTERFACE) {
				$member->setBody(null);
			}
			$this->methods[$member->getName()] = $member;

		} elseif ($member instanceof Property) {
			$this->properties[$member->getName()] = $member;

		} elseif ($member instanceof Constant) {
			$this->consts[$member->getName()] = $member;

		} else {
			throw new Nette\InvalidArgumentException('Argument must be Method|Property|Constant.');
		}

		return $this;
	}


	/**
	 * @param  Constant[]|mixed[]  $consts
	 * @return static
	 */
	public function setConstants(array $consts): self
	{
		$this->consts = [];
		foreach ($consts as $k => $v) {
			$const = $v instanceof Constant ? $v : (new Constant($k))->setValue($v);
			$this->consts[$const->getName()] = $const;
		}
		return $this;
	}


	/**
	 * @return Constant[]
	 */
	public function getConstants(): array
	{
		return $this->consts;
	}


	public function addConstant(string $name, $value): Constant
	{
		return $this->consts[$name] = (new Constant($name))->setValue($value);
	}


	/**
	 * @return static
	 */
	public function removeConstant(string $name): self
	{
		unset($this->consts[$name]);
		return $this;
	}


	/**
	 * @param  Property[]  $props
	 * @return static
	 */
	public function setProperties(array $props): self
	{
		$this->properties = [];
		foreach ($props as $v) {
			if (!$v instanceof Property) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Property[].');
			}
			$this->properties[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Property[]
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}


	public function getProperty(string $name): Property
	{
		if (!isset($this->properties[$name])) {
			throw new Nette\InvalidArgumentException("Property '$name' not found.");
		}
		return $this->properties[$name];
	}


	/**
	 * @param  string  $name  without $
	 */
	public function addProperty(string $name, $value = null): Property
	{
		return $this->properties[$name] = (new Property($name))->setValue($value);
	}


	/**
	 * @param  string  $name without $
	 * @return static
	 */
	public function removeProperty(string $name): self
	{
		unset($this->properties[$name]);
		return $this;
	}


	public function hasProperty(string $name): bool
	{
		return isset($this->properties[$name]);
	}


	/**
	 * @param  Method[]  $methods
	 * @return static
	 */
	public function setMethods(array $methods): self
	{
		$this->methods = [];
		foreach ($methods as $v) {
			if (!$v instanceof Method) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Method[].');
			}
			$this->methods[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Method[]
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}


	public function getMethod(string $name): Method
	{
		if (!isset($this->methods[$name])) {
			throw new Nette\InvalidArgumentException("Method '$name' not found.");
		}
		return $this->methods[$name];
	}


	public function addMethod(string $name): Method
	{
		$method = new Method($name);
		if ($this->type === self::TYPE_INTERFACE) {
			$method->setBody(null);
		} else {
			$method->setVisibility(self::VISIBILITY_PUBLIC);
		}
		return $this->methods[$name] = $method;
	}


	/**
	 * @return static
	 */
	public function removeMethod(string $name): self
	{
		unset($this->methods[$name]);
		return $this;
	}


	public function hasMethod(string $name): bool
	{
		return isset($this->methods[$name]);
	}


	/**
	 * @throws Nette\InvalidStateException
	 */
	public function validate(): void
	{
		if ($this->abstract && $this->final) {
			throw new Nette\InvalidStateException('Class cannot be abstract and final.');

		} elseif (!$this->name && ($this->abstract || $this->final)) {
			throw new Nette\InvalidStateException('Anonymous class cannot be abstract or final.');
		}
	}


	private function validateNames(array $names): void
	{
		foreach ($names as $name) {
			if (!Helpers::isNamespaceIdentifier($name, true)) {
				throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
			}
		}
	}


	public function __clone()
	{
		$clone = function ($item) { return clone $item; };
		$this->consts = array_map($clone, $this->consts);
		$this->properties = array_map($clone, $this->properties);
		$this->methods = array_map($clone, $this->methods);
	}
}
