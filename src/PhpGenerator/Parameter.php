<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Method parameter description.
 *
 * @property mixed $defaultValue
 */
final class Parameter
{
	use Nette\SmartObject;
	use Traits\NameAware;

	/** @var bool */
	private $reference = false;

	/** @var string|null */
	private $typeHint;

	/** @var bool */
	private $nullable = false;

	/** @var bool */
	private $hasDefaultValue = false;

	/** @var mixed */
	private $defaultValue;


	/**
	 * @return static
	 */
	public function setReference(bool $state = true): self
	{
		$this->reference = $state;
		return $this;
	}


	public function isReference(): bool
	{
		return $this->reference;
	}


	/**
	 * @param  string|null  $hint
	 * @return static
	 */
	public function setTypeHint($hint): self
	{
		$this->typeHint = $hint ? (string) $hint : null;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getTypeHint()
	{
		return $this->typeHint;
	}


	/**
	 * @deprecated  just use setDefaultValue()
	 * @return static
	 */
	public function setOptional(bool $state = true): self
	{
		$this->hasDefaultValue = $state;
		return $this;
	}


	/**
	 * @deprecated  use hasDefaultValue()
	 */
	public function isOptional(): bool
	{
		trigger_error(__METHOD__ . '() is deprecated, use hasDefaultValue()', E_USER_DEPRECATED);
		return $this->hasDefaultValue;
	}


	/**
	 * @return static
	 */
	public function setNullable(bool $state = true): self
	{
		$this->nullable = $state;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	/**
	 * @return static
	 */
	public function setDefaultValue($val): self
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
