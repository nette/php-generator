<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\TraitUse;


/**
 * @internal
 */
trait TraitsAware
{
	/** @var array<string, TraitUse> */
	private array $traits = [];


	/** @param  TraitUse[]  $traits */
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


	public function addTrait(string $name): TraitUse
	{
		if (isset($this->traits[$name])) {
			throw new Nette\InvalidStateException("Cannot add trait '$name', because it already exists.");
		}
		$this->traits[$name] = $trait = new TraitUse($name);
		if (func_num_args() > 1 && is_array(func_get_arg(1))) { // back compatibility
			array_map(fn($item) => $trait->addResolution($item), func_get_arg(1));
		}

		return $trait;
	}


	public function removeTrait(string $name): static
	{
		unset($this->traits[$name]);
		return $this;
	}
}
