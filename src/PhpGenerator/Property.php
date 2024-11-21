<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Type;


/**
 * Class property description.
 */
final class Property
{
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	private mixed $value = null;
	private bool $static = false;
	private ?string $type = null;
	private bool $nullable = false;
	private bool $initialized = false;
	private bool $readOnly = false;
	private ?Closure $getHook = null;
	private ?Closure $setHook = null;


	public function setValue(mixed $val): static
	{
		$this->value = $val;
		$this->initialized = true;
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


	public function setType(?string $type): static
	{
		$this->type = Helpers::validateType($type, $this->nullable);
		return $this;
	}


	/** @return ($asObject is true ? ?Type : ?string) */
	public function getType(bool $asObject = false): Type|string|null
	{
		return $asObject && $this->type
			? Type::fromString($this->type)
			: $this->type;
	}


	public function setNullable(bool $state = true): static
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable || ($this->initialized && $this->value === null);
	}


	public function setInitialized(bool $state = true): static
	{
		$this->initialized = $state;
		return $this;
	}


	public function isInitialized(): bool
	{
		return $this->initialized || $this->value !== null;
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

	public function setGetHook(?Closure $hook = null): static
	{
		$this->getHook = $hook;
		return $this;
	}

	public function getGetHook(): ?Closure
	{
		return $this->getHook;
	}

	public function hasGetHook(): bool
	{
		return $this->getHook !== null;
	}

	public function setSetHook(?Closure $hook = null): static
	{
		$this->setHook = $hook;
		return $this;
	}

	public function getSetHook(): ?Closure
	{
		return $this->setHook;
	}

	public function hasSetHook(): bool
	{
		return $this->setHook !== null;
	}

	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->readOnly && !$this->type) {
			throw new Nette\InvalidStateException("Property \$$this->name: Read-only properties are only supported on typed property.");
		}
	}
}
