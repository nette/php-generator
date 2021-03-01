<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Closure.
 *
 * @property string $body
 */
final class Closure
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\AttributeAware;

	/** @var Parameter[] */
	private $uses = [];


	public static function from(\Closure $closure): self
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($closure));
	}


	public function __toString(): string
	{
		return (new Printer)->printClosure($this);
	}


	/**
	 * @param  Parameter[]  $uses
	 * @return static
	 */
	public function setUses(array $uses): self
	{
		(function (Parameter ...$uses) {})(...$uses);
		$this->uses = $uses;
		return $this;
	}


	public function getUses(): array
	{
		return $this->uses;
	}


	public function addUse(string $name): Parameter
	{
		return $this->uses[] = new Parameter($name);
	}
}
