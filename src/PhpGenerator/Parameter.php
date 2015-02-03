<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Method parameter description.
 *
 * @author     David Grudl
 */
class Parameter extends Nette\Object
{
	/** @var string */
	private $name;

	/** @var bool */
	private $reference = FALSE;

	/** @var string */
	private $typeHint;

	/** @var bool */
	private $optional = FALSE;

	/** @var mixed */
	public $defaultValue;


	/**
	 * @return self
	 */
	public static function from(\ReflectionParameter $from)
	{
		$param = new static;
		$param->name = $from->getName();
		$param->reference = $from->isPassedByReference();
		try {
			$param->typeHint = $from->isArray() ? 'array' : ($from->getClass() ? '\\' . $from->getClass()->getName() : '');
		} catch (\ReflectionException $e) {
			if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
				$param->typeHint = '\\' . $m[1];
			} else {
				throw $e;
			}
		}
		$param->optional = PHP_VERSION_ID < 50407 ? $from->isOptional() || ($param->typeHint && $from->allowsNull()) : $from->isDefaultValueAvailable();
		$param->defaultValue = (PHP_VERSION_ID === 50316 ? $from->isOptional() : $from->isDefaultValueAvailable()) ? $from->getDefaultValue() : NULL;

		$namespace = $from->getDeclaringClass()->getNamespaceName();
		$namespace = $namespace ? "\\$namespace\\" : "\\";
		if (Nette\Utils\Strings::startsWith($param->typeHint, $namespace)) {
			$param->typeHint = substr($param->typeHint, strlen($namespace));
		}
		return $param;
	}


	/**
	 * @param  string  without $
	 * @return self
	 */
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
	 * @return self
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
	 * @param  string
	 * @return self
	 */
	public function setTypeHint($hint)
	{
		$this->typeHint = (string) $hint;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getTypeHint()
	{
		return $this->typeHint;
	}


	/**
	 * @param  bool
	 * @return self
	 */
	public function setOptional($state = TRUE)
	{
		$this->optional = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isOptional()
	{
		return $this->optional;
	}


	/**
	 * @return self
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

}
