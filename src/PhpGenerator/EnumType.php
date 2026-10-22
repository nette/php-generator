<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Definition of an enum with cases, methods, constants and traits.
 */
final class EnumType extends ClassLike
{
	use Traits\ConstantsAware;
	use Traits\MethodsAware;
	use Traits\TraitsAware;

	/** @var list<string> */
	private array $implements = [];

	/** @var array<string, EnumCase> */
	private array $cases = [];
	private ?string $type = null;


	/**
	 * Sets the backing type of a backed enum ('int' or 'string').
	 */
	public function setType(?string $type): static
	{
		$this->type = $type;
		return $this;
	}


	/**
	 * Returns the backing type ('int' or 'string'), or null for a pure enum.
	 */
	public function getType(): ?string
	{
		return $this->type;
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


	/**
	 * Replaces all cases.
	 * @param  list<EnumCase>  $cases
	 */
	public function setCases(array $cases): static
	{
		(function (EnumCase ...$cases) {})(...$cases);
		$this->cases = [];
		foreach ($cases as $case) {
			$this->cases[$case->getName()] = $case;
		}

		return $this;
	}


	/** @return array<string, EnumCase> */
	public function getCases(): array
	{
		return $this->cases;
	}


	/**
	 * Adds a case to the enum.
	 * @throws Nette\InvalidStateException if the case already exists and $overwrite is false
	 */
	public function addCase(string $name, string|int|Literal|null $value = null, bool $overwrite = false): EnumCase
	{
		if (!$overwrite && isset($this->cases[$name])) {
			throw new Nette\InvalidStateException("Cannot add cases '$name', because it already exists.");
		}
		return $this->cases[$name] = (new EnumCase($name))
			->setValue($value);
	}


	public function removeCase(string $name): static
	{
		unset($this->cases[$name]);
		return $this;
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		$name = $this->getName();
		$type = $this->type === null ? null : strtolower($this->type); // type names are case-insensitive
		if ($type !== null && !in_array($type, [Type::Int, Type::String], true)) {
			throw new Nette\InvalidStateException("Enum '$name': backing type must be int or string, '$this->type' given.");
		}

		$backed = $type !== null || Nette\Utils\Arrays::some($this->cases, fn(EnumCase $case) => $case->getValue() !== null);
		foreach ($this->cases as $case) {
			$value = $case->getValue();
			if ($value === null) {
				if ($backed) {
					throw new Nette\InvalidStateException("Case '{$case->getName()}' of backed enum '$name' must have a value.");
				}
			} elseif (!$value instanceof Literal) {
				$valueType = get_debug_type($value);
				$type ??= $valueType;
				if ($valueType !== $type) {
					throw new Nette\InvalidStateException("Case '{$case->getName()}' of enum '$name': value type $valueType does not match backing type $type.");
				}
			}
		}

		if ($backed && $type === null) {
			throw new Nette\InvalidStateException("Enum '$name': backing type cannot be inferred from case values, set it explicitly using setType().");
		}
	}


	/**
	 * Adds a member to the enum.
	 * @throws Nette\InvalidStateException if the member already exists and $overwrite is false
	 */
	public function addMember(Method|Constant|EnumCase|TraitUse $member, bool $overwrite = false): static
	{
		$name = $member->getName();
		[$type, $n] = match (true) {
			$member instanceof Constant => ['consts', $name],
			$member instanceof Method => ['methods', strtolower($name)],
			$member instanceof TraitUse => ['traits', $name],
			$member instanceof EnumCase => ['cases', $name],
		};
		if (!$overwrite && isset($this->$type[$n])) {
			throw new Nette\InvalidStateException("Cannot add member '$name', because it already exists.");
		}
		$this->$type[$n] = $member;
		return $this;
	}


	public function __clone(): void
	{
		parent::__clone();
		$this->consts = array_map(fn(Constant $c) => clone $c, $this->consts);
		$this->methods = array_map(fn(Method $m) => clone $m, $this->methods);
		$this->traits = array_map(fn(TraitUse $t) => clone $t, $this->traits);
		$this->cases = array_map(fn(EnumCase $c) => clone $c, $this->cases);
	}
}
