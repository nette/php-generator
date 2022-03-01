<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class/Interface/Trait/Enum description.
 */
abstract class ClassLike
{
	use Nette\SmartObject;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	public const
		VISIBILITY_PUBLIC = 'public',
		VISIBILITY_PROTECTED = 'protected',
		VISIBILITY_PRIVATE = 'private';

	private ?PhpNamespace $namespace;
	private ?string $name;


	public static function from(string|object $class, bool $withBodies = false, ?bool $materializeTraits = null): self
	{
		if ($materializeTraits !== null) {
			trigger_error(__METHOD__ . '() parameter $materializeTraits has been removed (is always false).', E_USER_DEPRECATED);
		}
		return (new Factory)
			->fromClassReflection(new \ReflectionClass($class), $withBodies);
	}


	/** @deprecated  use from(..., withBodies: true) */
	public static function withBodiesFrom(string|object $class): self
	{
		trigger_error(__METHOD__ . '() is deprecated, use from(..., withBodies: true)', E_USER_DEPRECATED);
		return (new Factory)
			->fromClassReflection(new \ReflectionClass($class), withBodies: true);
	}


	public static function fromCode(string $code): self
	{
		return (new Factory)
			->fromClassCode($code);
	}


	public function __construct(string $name, ?PhpNamespace $namespace = null)
	{
		$this->setName($name);
		$this->namespace = $namespace;
	}


	public function __toString(): string
	{
		return (new Printer)->printClass($this, $this->namespace);
	}


	/** @deprecated  an object can be in multiple namespaces */
	public function getNamespace(): ?PhpNamespace
	{
		return $this->namespace;
	}


	public function setName(?string $name): static
	{
		if ($name !== null && (!Helpers::isIdentifier($name) || isset(Helpers::KEYWORDS[strtolower($name)]))) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
		}

		$this->name = $name;
		return $this;
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	public function isClass(): bool
	{
		return $this instanceof ClassType;
	}


	public function isInterface(): bool
	{
		return $this instanceof InterfaceType;
	}


	public function isTrait(): bool
	{
		return $this instanceof TraitType;
	}


	public function isEnum(): bool
	{
		return $this instanceof EnumType;
	}


	/** @param  string[]  $names */
	protected function validateNames(array $names): void
	{
		foreach ($names as $name) {
			if (!Helpers::isNamespaceIdentifier($name, allowLeadingSlash: true)) {
				throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
			}
		}
	}


	public function validate(): void
	{
	}
}
