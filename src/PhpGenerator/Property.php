<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Type;


/**
 * Definition of a class property.
 */
final class Property
{
	use Traits\NameAware;
	use Traits\PropertyLike;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	private mixed $value = null;
	private bool $static = false;
	private ?Type $type = null;
	private bool $initialized = false;
	private bool $abstract = false;


	public function setValue(mixed $val): static
	{
		$this->value = $val;
		$this->initialized = true;
		$this->applyNullable();
		return $this;
	}


	public function &getValue(): mixed
	{
		return $this->value;
	}


	public function setStatic(bool $state = true): static
	{
		$this->static = $state;
		return $this;
	}


	public function isStatic(): bool
	{
		return $this->static;
	}


	public function setType(Type|string|null $type): static
	{
		$this->type = Helpers::createType($type);
		$this->applyNullable();
		return $this;
	}


	public function getType(): ?Type
	{
		return $this->type;
	}


	public function setNullable(bool $state = true): static
	{
		if (!$this->type) {
			throw new Nette\InvalidStateException('Call setType() before setNullable().');
		}
		$this->type = Helpers::nullableType($this->type, $state);
		$this->applyNullable();
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->type?->allows('null') ?? ($this->initialized && $this->value === null);
	}


	/** Moves a null initial value into the type. */
	private function applyNullable(): void
	{
		if ($this->type && $this->initialized && $this->value === null) {
			$this->type = $this->type->with('null');
		}
	}


	public function setInitialized(bool $state = true): static
	{
		$this->initialized = $state;
		$this->applyNullable();
		return $this;
	}


	public function isInitialized(): bool
	{
		return $this->initialized || $this->value !== null;
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


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->readOnly && !$this->type) {
			throw new Nette\InvalidStateException("Property \$$this->name: Read-only properties are only supported on typed property.");

		} elseif ($this->readOnly && $this->static) {
			throw new Nette\InvalidStateException("Property \$$this->name cannot be static and readonly at the same time.");

		} elseif ($this->readOnly && $this->isInitialized()) {
			throw new Nette\InvalidStateException("Property \$$this->name: Read-only property cannot have a default value.");

		} elseif ($this->abstract && $this->final) {
			throw new Nette\InvalidStateException("Property \$$this->name cannot be abstract and final at the same time.");

		} elseif (
			$this->abstract
			&& !Nette\Utils\Arrays::some($this->getHooks(), fn($hook) => $hook->isAbstract())
		) {
			throw new Nette\InvalidStateException("Property \$$this->name: Abstract property must have at least one abstract hook.");
		}
	}


	public function __clone(): void
	{
		$this->hooks = array_map(fn($item) => $item ? clone $item : $item, $this->hooks);
	}
}
