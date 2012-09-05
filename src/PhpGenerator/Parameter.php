<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\PhpGenerator;

use Nette;



/**
 * Method parameter description.
 *
 * @author     David Grudl
 *
 * @method Parameter setName(string $name)
 * @method Parameter setReference(bool $on)
 * @method Parameter setTypeHint(string $class)
 * @method Parameter setOptional(bool $on)
 * @method Parameter setDefaultValue(mixed $value)
 */
class Parameter extends Nette\Object
{
	/** @var string */
	public $name;

	/** @var bool */
	public $reference;

	/** @var string */
	public $typeHint;

	/** @var bool */
	public $optional;

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

		$namespace = /*5.2*PHP_VERSION_ID < 50300 ? '' : */$from->getDeclaringClass()->getNamespaceName();
		$namespace = $namespace ? "\\$namespace\\" : "\\";
		if (Nette\Utils\Strings::startsWith($param->typeHint, $namespace)) {
			$param->typeHint = substr($param->typeHint, strlen($namespace));
		}
		return $param;
	}



	public function __call($name, $args)
	{
		return Nette\ObjectMixin::callProperty($this, $name, $args);
	}

}
