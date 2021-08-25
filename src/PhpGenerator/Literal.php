<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;


/**
 * PHP literal value.
 */
class Literal
{
	/** @var string */
	private $value;


	public function __construct(string $value, array $args = null)
	{
		$this->value = $args === null
			? $value
			: (new Dumper)->format($value, ...$args);
	}


	public function __toString(): string
	{
		return $this->value;
	}
}
