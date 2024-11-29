<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette\PhpGenerator\Visibility;


/**
 * @internal
 */
trait VisibilityAware
{
	private ?Visibility $visibility = null;


	public function setVisibility(Visibility|string|null $value): static
	{
		$this->visibility = $value instanceof Visibility || $value === null
			? $value
			: Visibility::from($value);
		return $this;
	}


	public function getVisibility(): ?string
	{
		return $this->visibility?->value;
	}


	public function setPublic(): static
	{
		$this->visibility = Visibility::Public;
		return $this;
	}


	public function isPublic(): bool
	{
		return $this->visibility === Visibility::Public || $this->visibility === null;
	}


	public function setProtected(): static
	{
		$this->visibility = Visibility::Protected;
		return $this;
	}


	public function isProtected(): bool
	{
		return $this->visibility === Visibility::Protected;
	}


	public function setPrivate(): static
	{
		$this->visibility = Visibility::Private;
		return $this;
	}


	public function isPrivate(): bool
	{
		return $this->visibility === Visibility::Private;
	}
}
