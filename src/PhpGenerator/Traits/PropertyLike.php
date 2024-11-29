<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette\PhpGenerator\PropertyAccessMode;
use Nette\PhpGenerator\PropertyHook;
use Nette\PhpGenerator\PropertyHookType;
use Nette\PhpGenerator\Visibility;
use function array_filter, in_array, is_string;


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


	public function setVisibility(Visibility|string|null $get, Visibility|string|null $set = null): static
	{
		$this->visibility = [
			'set' => $set instanceof Visibility || $set === null ? $set : Visibility::from($set),
			'get' => $get instanceof Visibility || $get === null ? $get : Visibility::from($get),
		];
		return $this;
	}


	public function getVisibility(PropertyAccessMode|string $mode = PropertyAccessMode::Get): ?string
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return $this->visibility[$mode->value]?->value;
	}


	public function setPublic(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Public;
		return $this;
	}


	public function isPublic(PropertyAccessMode|string $mode = PropertyAccessMode::Get): bool
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return in_array($this->visibility[$mode->value], [Visibility::Public, null], true);
	}


	public function setProtected(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Protected;
		return $this;
	}


	public function isProtected(PropertyAccessMode|string $mode = PropertyAccessMode::Get): bool
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		return $this->visibility[$mode->value] === Visibility::Protected;
	}


	public function setPrivate(PropertyAccessMode|string $mode = PropertyAccessMode::Get): static
	{
		$mode = is_string($mode) ? PropertyAccessMode::from($mode) : $mode;
		$this->visibility[$mode->value] = Visibility::Private;
		return $this;
	}


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
	 * @param  PropertyHook[]  $hooks
	 */
	public function setHooks(array $hooks): static
	{
		(function (PropertyHook ...$hooks) {})(...$hooks);
		$this->hooks = $hooks;
		return $this;
	}


	/** @return array<string, PropertyHook> */
	public function getHooks(): array
	{
		return array_filter($this->hooks);
	}


	public function addHook(PropertyHookType|string $type, string $shortBody = ''): PropertyHook
	{
		$type = is_string($type) ? PropertyHookType::from($type) : $type;
		return $this->hooks[$type->value] = (new PropertyHook)
			->setBody($shortBody, short: true);
	}


	public function getHook(PropertyHookType|string $type): ?PropertyHook
	{
		$type = is_string($type) ? PropertyHookType::from($type) : $type;
		return $this->hooks[$type->value] ?? null;
	}


	public function hasHook(PropertyHookType|string $type): bool
	{
		$type = is_string($type) ? PropertyHookType::from($type) : $type;
		return isset($this->hooks[$type->value]);
	}
}
