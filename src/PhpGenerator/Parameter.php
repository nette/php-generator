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
 * Function/Method parameter description.
 *
 * @property mixed $defaultValue
 */
class Parameter
{
	use Nette\SmartObject;
	use Traits\NameAware;
	use Traits\AttributeAware;

	private bool $reference = false;
	private ?string $type = null;
	private bool $nullable = false;
	private bool $hasDefaultValue = false;
	private mixed $defaultValue = null;


	public function setReference(bool $state = true): static
	{
		$this->reference = $state;
		return $this;
	}


	public function isReference(): bool
	{
		return $this->reference;
	}


	public function setType(?string $type): static
	{
		$this->type = Helpers::validateType($type, $this->nullable);
		return $this;
	}


	public function getType(bool $asObject = false): Type|string|null
	{
		return $asObject && $this->type
			? Type::fromString($this->type)
			: $this->type;
	}


	/** @deprecated  use setType() */
	public function setTypeHint(?string $type): static
	{
		return $this->setType($type);
	}


	/** @deprecated  use getType() */
	public function getTypeHint(): ?string
	{
		return $this->getType();
	}


	/**
	 * @deprecated  just use setDefaultValue()
	 */
	public function setOptional(bool $state = true): static
	{
		trigger_error(__METHOD__ . '() is deprecated, use setDefaultValue()', E_USER_DEPRECATED);
		$this->hasDefaultValue = $state;
		return $this;
	}


	public function setNullable(bool $state = true): static
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	public function setDefaultValue(mixed $val): static
	{
		$this->defaultValue = $val;
		$this->hasDefaultValue = true;
		return $this;
	}


	public function getDefaultValue(): mixed
	{
		return $this->defaultValue;
	}


	public function hasDefaultValue(): bool
	{
		return $this->hasDefaultValue;
	}


	public function validate(): void
	{
	}
}
