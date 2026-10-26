<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Type;


/**
 * Definition of a function/method parameter.
 */
class Parameter
{
	use Traits\NameAware;
	use Traits\AttributeAware;
	use Traits\CommentAware;

	private bool $reference = false;
	private ?Type $type = null;
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
		return $this->type?->allows('null') ?? ($this->hasDefaultValue && $this->defaultValue === null);
	}


	/** Moves a null default into the type. */
	private function applyNullable(): void
	{
		if ($this->type && $this->hasDefaultValue && $this->defaultValue === null) {
			$this->type = $this->type->with('null');
		}
	}


	public function setDefaultValue(mixed $val): static
	{
		$this->defaultValue = $val;
		$this->hasDefaultValue = true;
		$this->applyNullable();
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
