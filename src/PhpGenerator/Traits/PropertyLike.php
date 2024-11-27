<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette\PhpGenerator\PropertyHook;
use Nette\PhpGenerator\PropertyHookType;


/**
 * @internal
 */
trait PropertyLike
{
	use VisibilityAware;

	private bool $readOnly = false;

	/** @var array<string, ?PropertyHook> */
	private array $hooks = [PropertyHookType::Set => null, PropertyHookType::Get => null];


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


	/** @param  'set'|'get'  $type */
	public function addHook(string $type, string $shortBody = ''): PropertyHook
	{
		return $this->hooks[PropertyHookType::from($type)] = (new PropertyHook)
			->setBody($shortBody, short: true);
	}


	/** @param  'set'|'get'  $type */
	public function getHook(string $type): ?PropertyHook
	{
		return $this->hooks[PropertyHookType::from($type)] ?? null;
	}


	/** @param  'set'|'get'  $type */
	public function hasHook(string $type): bool
	{
		return isset($this->hooks[PropertyHookType::from($type)]);
	}
}
