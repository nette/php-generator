<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Type;


/**
 * Definition of a class constant.
 */
final class Constant
{
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;
	use Traits\AttributeAware;

	private mixed $value;
	private bool $final = false;
	private ?Type $type = null;


	public function setValue(mixed $val): static
	{
		$this->value = $val;
		return $this;
	}


	public function getValue(): mixed
	{
		return $this->value;
	}


	public function setFinal(bool $state = true): static
	{
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	public function setType(Type|string|null $type): static
	{
		$this->type = Helpers::createType($type);
		return $this;
	}


	public function getType(): ?Type
	{
		return $this->type;
	}


	/** @throws Nette\InvalidStateException */
	public function validate(): void
	{
		if ($this->final && $this->isPrivate()) {
			throw new Nette\InvalidStateException("Constant $this->name cannot be final and private at the same time.");
		}
	}
}
