<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\TraitUse;
use function array_map, func_get_arg, func_num_args, is_array;


/**
 * @internal
 */
trait TraitsAware
{
	/** @var array<string, TraitUse> */
	private array $traits = [];


	/**
	 * Replaces all traits.
	 * @param  TraitUse[]  $traits
	 */
	public function setTraits(array $traits): static
	{
		(function (TraitUse ...$traits) {})(...$traits);
		$this->traits = [];
		foreach ($traits as $trait) {
			$this->traits[$trait->getName()] = $trait;
		}

		return $this;
	}


	/** @return TraitUse[] */
	public function getTraits(): array
	{
		return $this->traits;
	}


	/**
	 * Adds a method. If it already exists, throws an exception.
	 */
	public function addTrait(string $name): TraitUse
	{
		if (isset($this->traits[$name])) {
			throw new Nette\InvalidStateException("Cannot add trait '$name', because it already exists.");
		}
		$this->traits[$name] = $trait = new TraitUse($name);
		if (func_num_args() > 1 && is_array(func_get_arg(1))) { // back compatibility
			trigger_error('Passing second argument to ' . __METHOD__ . '() is deprecated, use addResolution() instead.');
			array_map(fn($item) => $trait->addResolution($item), func_get_arg(1));
		}

		return $trait;
	}


	public function removeTrait(string $name): static
	{
		unset($this->traits[$name]);
		return $this;
	}


	public function hasTrait(string $name): bool
	{
		return isset($this->traits[$name]);
	}
}
