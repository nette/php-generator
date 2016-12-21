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

	/** @var string */
	private $name = '';

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
	 * @return static
	 */
	public static function from(\ReflectionParameter $from)
	{
		$param = new static($from->getName());
		$param->reference = $from->isPassedByReference();
		if (PHP_VERSION_ID >= 70000) {
			$param->typeHint = $from->hasType() ? (string) $from->getType() : NULL;
			$param->nullable = $from->hasType() && $from->getType()->allowsNull();
		} elseif ($from->isArray() || $from->isCallable()) {
			$param->typeHint = $from->isArray() ? 'array' : 'callable';
		} else {
			try {
				$param->typeHint = $from->getClass() ? $from->getClass()->getName() : NULL;
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					$param->typeHint = $m[1];
				} else {
					throw $e;
				}
			}
		}
		$param->hasDefaultValue = $from->isDefaultValueAvailable();
		$param->defaultValue = $from->isDefaultValueAvailable() ? $from->getDefaultValue() : NULL;
		$param->nullable = $param->nullable && (!$param->hasDefaultValue || $param->defaultValue !== NULL);
		return $param;
	}


	/**
	 * @param  string  without $
	 */
	public function __construct($name = '')
	{
		$this->setName($name);
	}


	/** @deprecated */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


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
