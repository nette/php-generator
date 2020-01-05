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

	/** @var mixed */
	private $value;

	/** @var bool */
	private $static = false;

	/** @var string|null */
	private $type;

	/** @var bool */
	private $nullable = false;

	/** @var bool */
	private $initialized = false;


	/** @return static */
	public function setValue($val): self
	{
		$this->value = $val;
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
	public function setType(?string $val): self
	{
		$this->type = $val;
		return $this;
	}


	public function getType(): ?string
	{
		return $this->type;
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
		return $this->initialized;
	}
}
