<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class method.
 *
 * @property string|null $body
 */
final class Method
{
	use Nette\SmartObject;
	use Traits\FunctionLike;
	use Traits\NameAware;
	use Traits\VisibilityAware;
	use Traits\CommentAware;

	/** @var string|null */
	private $body = '';

	/** @var bool */
	private $static = false;

	/** @var bool */
	private $final = false;

	/** @var bool */
	private $abstract = false;


	/**
	 * @param  string|array  $method
	 * @return static
	 */
	public static function from($method): self
	{
		return (new Factory)->fromMethodReflection(Nette\Utils\Callback::toReflection($method));
	}


	public function __construct(string $name)
	{
		if (!Helpers::isIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid name.");
		}
		$this->name = $name;
	}


	public function __toString(): string
	{
		return (new Printer)->printMethod($this);
	}


	/**
	 * @return static
	 */
	public function setBody(?string $code, array $args = null): self
	{
		$this->body = $args === null || $code === null ? $code : Helpers::formatArgs($code, $args);
		return $this;
	}


	public function getBody(): ?string
	{
		return $this->body;
	}


	/**
	 * @return static
	 */
	public function setStatic(bool $state = true): self
	{
		$this->static = $state;
		return $this;
	}


	public function isStatic(): bool
	{
		return $this->static;
	}


	/**
	 * @return static
	 */
	public function setFinal(bool $state = true): self
	{
		if ($state && $this->isAbstract()) {
			throw new Nette\InvalidStateException('Method cannot be final and abstract.');
		}
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	/**
	 * @return static
	 */
	public function setAbstract(bool $state = true): self
	{
		if ($state && $this->isFinal()) {
			throw new Nette\InvalidStateException('Method cannot be final and abstract.');
		}
		$this->abstract = $state;
		return $this;
	}


	public function isAbstract(): bool
	{
		return $this->abstract;
	}
}
