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
 *
 * @method Parameter setName(string)
 * @method string getName()
 * @method Parameter setReference(bool)
 * @method bool isReference()
 * @method Parameter setTypeHint(string)
 * @method string getTypeHint()
 * @method Parameter setOptional(bool)
 * @method bool isOptional()
 * @method Parameter setDefaultValue(mixed)
 * @method mixed getDefaultValue()
 */
class Parameter extends Nette\Object
{
	/** @var string */
	private $name;

	/** @var bool */
	private $reference;

	/** @var string */
	private $typeHint;

	/** @var bool */
	private $optional;

	/** @var mixed */
	public $defaultValue;


	/** @return Parameter */
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

}
