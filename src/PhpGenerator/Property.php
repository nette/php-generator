<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


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

	private mixed $value;

	private bool $static = false;

	private ?string $type = null;

	private bool $nullable = false;

	private bool $initialized = false;


	public function setValue($val): static
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


	public function setNullable(bool $state = true): static
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
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
}
