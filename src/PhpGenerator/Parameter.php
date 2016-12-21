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
 */
class Parameter
{
	use Nette\SmartObject;
	use Traits\NameAware;

	/** @var bool */
	private $reference = FALSE;

	/** @var string|NULL */
	private $typeHint;

	/** @var bool */
	private $nullable = FALSE;

	/** @var bool */
	private $hasDefaultValue = FALSE;

	/** @var mixed */
	public $defaultValue;


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReference($state = TRUE)
	{
		$this->reference = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isReference()
	{
		return $this->reference;
	}


	/**
	 * @param  string|NULL
	 * @return static
	 */
	public function setTypeHint($hint)
	{
		$this->typeHint = $hint ? (string) $hint : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getTypeHint()
	{
		return $this->typeHint;
	}


	/**
	 * @deprecated  just use setDefaultValue()
	 * @param  bool
	 * @return static
	 */
	public function setOptional($state = TRUE)
	{
		$this->hasDefaultValue = (bool) $state;
		return $this;
	}


	/**
	 * @deprecated  use hasDefaultValue()
	 * @return bool
	 */
	public function isOptional()
	{
		trigger_error(__METHOD__ . '() is deprecated, use hasDefaultValue()', E_USER_DEPRECATED);
		return $this->hasDefaultValue;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setNullable($state = TRUE)
	{
		$this->nullable = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isNullable()
	{
		return $this->nullable;
	}


	/**
	 * @return static
	 */
	public function setDefaultValue($val)
	{
		$this->defaultValue = $val;
		$this->hasDefaultValue = TRUE;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}


	/**
	 * @return bool
	 */
	public function hasDefaultValue()
	{
		return $this->hasDefaultValue;
	}

}
