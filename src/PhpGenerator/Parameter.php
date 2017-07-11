<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Method parameter description.
 */
class Parameter
{
	use Nette\SmartObject;
	use Traits\NameAware;

	/** @var mixed */
	public $defaultValue;

	/** @var bool */
	private $reference = false;

	/** @var string|null */
	private $typeHint;

	/** @var bool */
	private $nullable = false;

	/** @var bool */
	private $hasDefaultValue = false;


	/**
	 * @deprecated
	 * @return static
	 */
	public static function from(\ReflectionParameter $from)
	{
		trigger_error(__METHOD__ . '() is deprecated, use Nette\PhpGenerator\Factory.', E_USER_DEPRECATED);
		return (new Factory)->fromParameterReflection($from);
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReference($state = true)
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
	 * @param  string|null
	 * @return static
	 */
	public function setTypeHint($hint)
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
	 * @param  bool
	 * @return static
	 */
	public function setOptional($state = true)
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
		return $this->hasDefaultValue;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setNullable($state = true)
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
