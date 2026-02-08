<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Definition of a trait use statement.
 */
final class TraitUse
{
	use Traits\NameAware;
	use Traits\CommentAware;

	/** @var list<string> */
	private array $resolutions = [];


	public function __construct(string $name)
	{
		if (!Nette\PhpGenerator\Helpers::isNamespaceIdentifier($name, allowLeadingSlash: true)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid trait name.");
		}

		$this->name = $name;
	}


	public function addResolution(string $resolution): static
	{
		$this->resolutions[] = $resolution;
		return $this;
	}


	/** @return list<string> */
	public function getResolutions(): array
	{
		return $this->resolutions;
	}
}
