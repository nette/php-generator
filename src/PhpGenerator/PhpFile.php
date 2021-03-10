<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * Instance of PHP file.
 *
 * Generates:
 * - opening tag (<?php)
 * - doc comments
 * - one or more namespaces
 */
final class PhpFile
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	/** @var PhpNamespace[] */
	private array $namespaces = [];

	private bool $strictTypes = false;


	public function addClass(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addClass(Helpers::extractShortName($name));
	}


	public function addInterface(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addInterface(Helpers::extractShortName($name));
	}


	public function addTrait(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addTrait(Helpers::extractShortName($name));
	}


	public function addNamespace(string|PhpNamespace $namespace): PhpNamespace
	{
		if ($namespace instanceof PhpNamespace) {
			$res = $this->namespaces[$namespace->getName()] = $namespace;

		} elseif (is_string($namespace)) {
			$res = $this->namespaces[$namespace] ??= new PhpNamespace($namespace);
		}

		foreach ($this->namespaces as $namespace) {
			$namespace->setBracketedSyntax(count($this->namespaces) > 1 && isset($this->namespaces['']));
		}
		return $res;
	}


	/** @return PhpNamespace[] */
	public function getNamespaces(): array
	{
		return $this->namespaces;
	}


	public function addUse(string $name, string $alias = null): static
	{
		$this->addNamespace('')->addUse($name, $alias);
		return $this;
	}


	/**
	 * Adds declare(strict_types=1) to output.
	 */
	public function setStrictTypes(bool $on = true): static
	{
		$this->strictTypes = $on;
		return $this;
	}


	public function hasStrictTypes(): bool
	{
		return $this->strictTypes;
	}


	/** @deprecated  use hasStrictTypes() */
	public function getStrictTypes(): bool
	{
		return $this->strictTypes;
	}


	public function __toString(): string
	{
		return (new Printer)->printFile($this);
	}
}
