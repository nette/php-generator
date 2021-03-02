<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


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
		if ($type && $type[0] === '?') {
			$type = substr($type, 1);
			$this->nullable = true;
		}
		$this->type = $type;
		return $this;
	}


	public function getType(): ?string
	{
		return $this->type;
	}


	/** @deprecated  use setType() */
	public function setTypeHint(?string $type): static
	{
		$this->type = $type;
		return $this;
	}


	/** @deprecated  use getType() */
	public function getTypeHint(): ?string
	{
		return $this->type;
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


	public function setDefaultValue($val): static
	{
		$this->defaultValue = $val;
		$this->hasDefaultValue = true;
		return $this;
	}


	public function getDefaultValue()
	{
		return $this->defaultValue;
	}


	public function hasDefaultValue(): bool
	{
		return $this->hasDefaultValue;
	}
}
