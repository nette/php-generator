<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator\Traits;

use Nette\PhpGenerator\{PropertyAccessMode, PropertyHook, Visibility};
use function is_string;


/**
 * @internal
 */
trait PropertyLike
{
	/** @var array{set: ?Visibility, get: ?Visibility} */
	private array $visibility = ['set' => null, 'get' => null];
	private bool $final = false;
	private bool $readOnly = false;

	/** @var array<string, ?PropertyHook> */
	private array $hooks = ['set' => null, 'get' => null];


	/**
	 * Sets asymmetric visibility for get and set access modes.
	 * @param  Visibility|'public'|'protected'|'private'|null  $get
	 * @param  Visibility|'public'|'protected'|'private'|null  $set
	 */
	public function setVisibility(Visibility|string|null $get, Visibility|string|null $set = null): static
	{
		$this->visibility = [
			'set' => $set instanceof Visibility || $set === null ? $set : Visibility::from($set),
			'get' => $get instanceof Visibility || $get === null ? $get : Visibility::from($get),
		];
		return $this;
	}


	/** @return 'public'|'protected'|'private'|null */
	public function getVisibility(PropertyAccessMode|string $mode = PropertyAccessMode::Get): ?string
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return $this->visibility[$mode->value]?->value;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function setPublic(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Public;
		return $this;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function isPublic(PropertyAccessMode|string $mode = PropertyAccessMode::Get): bool
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return in_array($this->visibility[$mode->value], [Visibility::Public, null], true);
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function setProtected(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Protected;
		return $this;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function isProtected(PropertyAccessMode|string $mode = PropertyAccessMode::Get): bool
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return $this->visibility[$mode->value] === Visibility::Protected;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function setPrivate(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Private;
		return $this;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $mode */
	public function isPrivate(PropertyAccessMode|string $mode = PropertyAccessMode::Get): bool
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return $this->visibility[$mode->value] === Visibility::Private;
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


	public function setReadOnly(bool $state = true): static
	{
		$this->readOnly = $state;
		return $this;
	}


	public function isReadOnly(): bool
	{
		return $this->readOnly;
	}


	/**
	 * Replaces all hooks.
	 * @param  array<string, PropertyHook>  $hooks  indexed by hook type ('set'|'get')
	 */
	public function setHooks(array $hooks): static
	{
		(function (PropertyHook ...$hooks) {})(...$hooks);
		$this->hooks = [];
		foreach ($hooks as $type => $hook) { // preserves caller order of hooks
			$this->hooks[PropertyAccessMode::from((string) $type)->value] = $hook;
		}

		$this->hooks += ['set' => null, 'get' => null];
		return $this;
	}


	/** @return array<string, PropertyHook> */
	public function getHooks(): array
	{
		return array_filter($this->hooks);
	}


	/**
	 * Adds a get or set hook. When $shortBody is provided, it is used as an arrow-function-style body.
	 * @param  PropertyAccessMode|'set'|'get'  $type
	 */
	public function addHook(PropertyAccessMode|string $type, string $shortBody = ''): PropertyHook
	{
		$type = is_string($type) ? PropertyAccessMode::from($type) : $type;
		return $this->hooks[$type->value] = (new PropertyHook)
			->setBody($shortBody, short: true);
	}


	/** @param  PropertyAccessMode|'set'|'get'  $type */
	public function getHook(PropertyAccessMode|string $type): ?PropertyHook
	{
		$type = is_string($type) ? PropertyAccessMode::from($type) : $type;
		return $this->hooks[$type->value] ?? null;
	}


	/** @param  PropertyAccessMode|'set'|'get'  $type */
	public function hasHook(PropertyAccessMode|string $type): bool
	{
		$type = is_string($type) ? PropertyAccessMode::from($type) : $type;
		return isset($this->hooks[$type->value]);
	}
}
