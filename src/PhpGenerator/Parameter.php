<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Utils\PhpGenerator;

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
		$param->optional = $from->isOptional() || $from->allowsNull();
		$param->defaultValue = $from->isOptional() ? $from->getDefaultValue() : NULL; // PHP bug #62988
		try {
			$param->typeHint = $from->isArray() ? 'array' : ($from->getClass() ? '\\' . $from->getClass()->getName() : '');
		} catch (\ReflectionException $e) {
			if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
				$param->typeHint = '\\' . $m[1];
			} else {
				throw $e;
			}
		}
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
