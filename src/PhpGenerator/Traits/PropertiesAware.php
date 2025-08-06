<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\Property;
use function func_num_args;


/**
 * @internal
 */
trait PropertiesAware
{
	/** @var array<string, Property> */
	private array $properties = [];


	/**
	 * Replaces all properties.
	 * @param  Property[]  $props
	 */
	public function setProperties(array $props): static
	{
		(function (Property ...$props) {})(...$props);
		$this->properties = [];
		foreach ($props as $v) {
			$this->properties[$v->getName()] = $v;
		}

		return $this;
	}


	/** @return Property[] */
	public function getProperties(): array
	{
		return $this->properties;
	}


	public function getProperty(string $name): Property
	{
		return $this->properties[$name] ?? throw new Nette\InvalidArgumentException("Property '$name' not found.");
	}


	/**
	 * Adds a property. If it already exists, throws an exception or overwrites it if $overwrite is true.
	 * @param  string  $name  without $
	 */
	public function addProperty(string $name, mixed $value = null, bool $overwrite = false): Property
	{
		if (!$overwrite && isset($this->properties[$name])) {
			throw new Nette\InvalidStateException("Cannot add property '$name', because it already exists.");
		}
		return $this->properties[$name] = func_num_args() > 1
			? (new Property($name))->setValue($value)
			: new Property($name);
	}


	/** @param  string  $name without $ */
	public function removeProperty(string $name): static
	{
		unset($this->properties[$name]);
		return $this;
	}


	public function hasProperty(string $name): bool
	{
		return isset($this->properties[$name]);
	}
}
