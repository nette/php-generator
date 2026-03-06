<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;


/**
 * Definition of a closure.
 */
final class Closure
{
	use Traits\FunctionLike;
	use Traits\AttributeAware;

	/** @var list<Parameter> */
	private array $uses = [];


	/**
	 * Creates an instance from a closure reflection.
	 * @param  \Closure(): mixed  $closure
	 */
	public static function from(\Closure $closure): self
	{
		return (new Factory)->fromFunctionReflection(new \ReflectionFunction($closure));
	}


	public function __toString(): string
	{
		return (new Printer)->printClosure($this);
	}


	/**
	 * Replaces all uses.
	 * @param  list<Parameter>  $uses
	 */
	public function setUses(array $uses): static
	{
		(function (Parameter ...$uses) {})(...$uses);
		$this->uses = $uses;
		return $this;
	}


	/** @return list<Parameter> */
	public function getUses(): array
	{
		return $this->uses;
	}


	/**
	 * Adds a variable binding to the closure's use list.
	 */
	public function addUse(string $name): Parameter
	{
		return $this->uses[] = new Parameter($name);
	}


	public function __clone(): void
	{
		$this->parameters = array_map(fn($param) => clone $param, $this->parameters);
	}
}
