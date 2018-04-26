<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Closure.
 *
 * @property string $body
 */
class Closure
{
	use Nette\SmartObject;
	use Traits\FunctionLike;

	/** @var Parameter[] */
	private $uses = [];


	/**
	 * @return static
	 */
	public static function from(\Closure $closure)
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($closure));
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$uses = [];
		foreach ($this->uses as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}
		$useStr = strlen($tmp = implode(', ', $uses)) > Helpers::WRAP_LENGTH && count($uses) > 1
			? "\n\t" . implode(",\n\t", $uses) . "\n"
			: $tmp;

		return 'function '
			. ($this->returnReference ? '&' : '')
			. $this->parametersToString()
			. ($this->uses ? " use ($useStr)" : '')
			. $this->returnTypeToString()
			. " {\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}';
	}


	/**
	 * @param  Parameter[]
	 * @return static
	 */
	public function setUses(array $uses)
	{
		foreach ($uses as $use) {
			if (!$use instanceof Parameter) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Parameter[].');
			}
		}
		$this->uses = $uses;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getUses()
	{
		return $this->uses;
	}


	/**
	 * @return Parameter
	 */
	public function addUse($name)
	{
		return $this->uses[] = new Parameter($name);
	}
}
