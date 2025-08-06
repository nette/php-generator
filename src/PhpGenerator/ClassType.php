<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use function array_diff, array_map, func_num_args, strtolower;


/**
 * Definition of a class with properties, methods, constants, traits and PHP attributes.
 */
final class ClassType extends ClassLike
{
	use Traits\ConstantsAware;
	use Traits\MethodsAware;
	use Traits\PropertiesAware;
	use Traits\TraitsAware;

	#[\Deprecated]
	public const
		TYPE_CLASS = 'class',
		TYPE_INTERFACE = 'interface',
		TYPE_TRAIT = 'trait',
		TYPE_ENUM = 'enum';

	private bool $final = false;
	private bool $abstract = false;
	private ?string $extends = null;
	private bool $readOnly = false;

	/** @var list<string> */
	private array $implements = [];


	public function __construct(?string $name = null)
	{
		parent::__construct($name ?? 'foo', func_num_args() > 1 ? func_get_arg(1) : null); // backward compatibility
		if ($name === null) {
			$this->setName(null);
		}
	}


	public function setFinal(bool $state = true): static
	{
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	public function setAbstract(bool $state = true): static
	{
		$this->abstract = $state;
		return $this;
	}


	public function isAbstract(): bool
	{
		return $this->abstract;
	}


	public function setReadOnly(bool $state = true): static
	{
		$this->readOnly = $state;
		return $this;
	}


	public function isReadOnly(): bool
	{
		return $this->readOnly;
	}


	public function setExtends(?string $name): static
	{
		if ($name) {
			$this->validateNames([$name]);
		}
		$this->extends = $name;
		return $this;
	}


	public function getExtends(): ?string
	{
		return $this->extends;
	}


	/** @param list<string>  $names */
	public function setImplements(array $names): static
	{
		$this->validateNames($names);
		$this->implements = $names;
		return $this;
	}


	/** @return list<string> */
	public function getImplements(): array
	{
		return $this->implements;
	}


	public function addImplement(string $name): static
	{
		$this->validateNames([$name]);
		$this->implements[] = $name;
		return $this;
	}


	public function removeImplement(string $name): static
	{
		$this->implements = array_values(array_diff($this->implements, [$name]));
		return $this;
	}


	public function addMember(Method|Property|Constant|TraitUse $member, bool $overwrite = false): static
	{
		$name = $member->getName();
		[$type, $n] = match (true) {
			$member instanceof Constant => ['consts', $name],
			$member instanceof Method => ['methods', strtolower($name)],
			$member instanceof Property => ['properties', $name],
			$member instanceof TraitUse => ['traits', $name],
		};
		if (!$overwrite && isset($this->$type[$n])) {
			throw new Nette\InvalidStateException("Cannot add member '$name', because it already exists.");
		}
		$this->$type[$n] = $member;
		return $this;
	}


	/**
	 * @deprecated use ClassManipulator::inheritProperty()
	 */
	public function inheritProperty(string $name, bool $returnIfExists = false): Property
	{
		return (new ClassManipulator($this))->inheritProperty($name, $returnIfExists);
	}


	/**
	 * @deprecated use ClassManipulator::inheritMethod()
	 */
	public function inheritMethod(string $name, bool $returnIfExists = false): Method
	{
		return (new ClassManipulator($this))->inheritMethod($name, $returnIfExists);
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		$name = $this->getName();
		if ($name === null && ($this->abstract || $this->final)) {
			throw new Nette\InvalidStateException('Anonymous class cannot be abstract or final.');

		} elseif ($this->abstract && $this->final) {
			throw new Nette\InvalidStateException("Class '$name' cannot be abstract and final at the same time.");
		}
	}


	public function __clone(): void
	{
		parent::__clone();
		$this->consts = array_map(fn(Constant $c) => clone $c, $this->consts);
		$this->methods = array_map(fn(Method $m) => clone $m, $this->methods);
		$this->properties = array_map(fn(Property $p) => clone $p, $this->properties);
		$this->traits = array_map(fn(TraitUse $t) => clone $t, $this->traits);
	}
}
