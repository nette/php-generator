<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Definition of a PHP attribute.
 */
final class Attribute
{
	public function __construct(
		private readonly string $name,
		/** @var mixed[] */
		private readonly array $args,
	) {
		if (!Helpers::isNamespaceIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid attribute name.");
		}
	}


	public function getName(): string
	{
		return $this->name;
	}


	/** @return mixed[] */
	public function getArguments(): array
	{
		return $this->args;
	}
}
