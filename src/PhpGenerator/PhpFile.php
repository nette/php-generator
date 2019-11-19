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
	private $namespaces = [];

	/** @var bool */
	private $strictTypes = false;


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


	public function addNamespace(string $name): PhpNamespace
	{
		if (!isset($this->namespaces[$name])) {
			$this->namespaces[$name] = new PhpNamespace($name);
			foreach ($this->namespaces as $namespace) {
				$namespace->setBracketedSyntax(count($this->namespaces) > 1 && isset($this->namespaces['']));
			}
		}
		return $this->namespaces[$name];
	}


	/**
	 * @return PhpNamespace[]
	 */
	public function getNamespaces(): array
	{
		return $this->namespaces;
	}


	/**
	 * @return static
	 */
	public function addUse(string $name, string $alias = null): self
	{
		$this->addNamespace('')->addUse($name, $alias);
		return $this;
	}


	/**
	 * Adds declare(strict_types=1) to output.
	 * @return static
	 */
	public function setStrictTypes(bool $on = true): self
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
		try {
			return (new Printer)->printFile($this);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}
}
