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
 *
 * @property mixed $value
 */
final class Property
{
	use Nette\SmartObject;
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


	/** @return static */
	public function setValue($val): self
	{
		$this->value = $val;
		$this->initialized = true;
		return $this;
	}


	public function &getValue()
	{
		return $this->value;
	}


	/** @return static */
	public function setStatic(bool $state = true): self
	{
		$this->static = $state;
		return $this;
	}


	public function isStatic(): bool
	{
		return $this->static;
	}


	/** @return static */
	public function setType(?string $type): self
	{
		$this->type = Helpers::validateType($type, $this->nullable);
		return $this;
	}


	/**
	 * @return Type|string|null
	 */
	public function getType(bool $asObject = false)
	{
		return $asObject && $this->type
			? Type::fromString($this->type)
			: $this->type;
	}


	/** @return static */
	public function setNullable(bool $state = true): self
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	/** @return static */
	public function setInitialized(bool $state = true): self
	{
		$this->initialized = $state;
		return $this;
	}


	public function isInitialized(): bool
	{
		return $this->initialized || $this->value !== null;
	}


	/** @return static */
	public function setReadOnly(bool $state = true): self
	{
		$this->readOnly = $state;
		return $this;
	}


	public function isReadOnly(): bool
	{
		return $this->readOnly;
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->readOnly && !$this->type) {
			throw new Nette\InvalidStateException("Property \$$this->name: Read-only properties are only supported on typed property.");
		}
	}
}
