<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use function count;


/**
 * Represents a complete PHP file with namespaces, classes, and functions.
 */
final class PhpFile
{
	use Traits\CommentAware;

	/** @var array<string, PhpNamespace> */
	private array $namespaces = [];
	private bool $strictTypes = false;


	/**
	 * Parses PHP source code and returns the resulting PhpFile representation.
	 */
	public static function fromCode(string $code): self
	{
		return (new Factory)->fromCode($code);
	}


	/**
	 * Adds a namespace, class-like type, or function to the file.
	 * Class-like types and functions are placed into their respective namespace (created if needed).
	 */
	public function add(ClassType|InterfaceType|TraitType|EnumType|GlobalFunction|PhpNamespace $item): static
	{
		if ($item instanceof PhpNamespace) {
			if (isset($this->namespaces[$name = $item->getName()])) {
				throw new Nette\InvalidStateException("Namespace '$name' already exists in the file.");
			}
			$this->namespaces[$name] = $item;
			$this->refreshBracketedSyntax();

		} elseif ($item instanceof GlobalFunction) {
			$this->addNamespace('')->add($item);

		} else {
			$this->addNamespace($item->getNamespace()?->getName() ?? '')->add($item);
		}

		return $this;
	}


	/**
	 * Adds a class to the file. Accepts the fully qualified name including namespace.
	 * @throws Nette\InvalidStateException if the class already exists
	 */
	public function addClass(string $name): ClassType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addClass(Helpers::extractShortName($name));
	}


	/**
	 * Adds an interface to the file. Accepts the fully qualified name including namespace.
	 * @throws Nette\InvalidStateException if the interface already exists
	 */
	public function addInterface(string $name): InterfaceType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addInterface(Helpers::extractShortName($name));
	}


	/**
	 * Adds a trait to the file. Accepts the fully qualified name including namespace.
	 * @throws Nette\InvalidStateException if the trait already exists
	 */
	public function addTrait(string $name): TraitType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addTrait(Helpers::extractShortName($name));
	}


	/**
	 * Adds an enum to the file. Accepts the fully qualified name including namespace.
	 * @throws Nette\InvalidStateException if the enum already exists
	 */
	public function addEnum(string $name): EnumType
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addEnum(Helpers::extractShortName($name));
	}


	/**
	 * Adds a function to the file. Accepts the fully qualified name including namespace.
	 * @throws Nette\InvalidStateException if the function already exists
	 */
	public function addFunction(string $name): GlobalFunction
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addFunction(Helpers::extractShortName($name));
	}


	/**
	 * Adds a namespace to the file, or returns the existing one if it already exists.
	 */
	public function addNamespace(string|PhpNamespace $namespace): PhpNamespace
	{
		$res = $namespace instanceof PhpNamespace
			? ($this->namespaces[$namespace->getName()] = $namespace)
			: ($this->namespaces[$namespace] ??= new PhpNamespace($namespace));

		$this->refreshBracketedSyntax();
		return $res;
	}


	/**
	 * Removes a namespace from the file.
	 */
	public function removeNamespace(string|PhpNamespace $namespace): static
	{
		$name = $namespace instanceof PhpNamespace ? $namespace->getName() : $namespace;
		unset($this->namespaces[$name]);
		return $this;
	}


	/** @return array<string, PhpNamespace> */
	public function getNamespaces(): array
	{
		return $this->namespaces;
	}


	/** @return array<string, ClassType|InterfaceType|TraitType|EnumType>  fully qualified name => class */
	public function getClasses(): array
	{
		$classes = [];
		foreach ($this->namespaces as $n => $namespace) {
			$n .= $n ? '\\' : '';
			foreach ($namespace->getClasses() as $c => $class) {
				$classes[$n . $c] = $class;
			}
		}

		return $classes;
	}


	/** @return array<string, GlobalFunction>  fully qualified name => function */
	public function getFunctions(): array
	{
		$functions = [];
		foreach ($this->namespaces as $n => $namespace) {
			$n .= $n ? '\\' : '';
			foreach ($namespace->getFunctions() as $f => $function) {
				$functions[$n . $f] = $function;
			}
		}

		return $functions;
	}


	/**
	 * Adds a use statement to the root (unnamed) namespace.
	 * @param  PhpNamespace::Name*  $of
	 */
	public function addUse(string $name, ?string $alias = null, string $of = PhpNamespace::NameNormal): static
	{
		$this->addNamespace('')->addUse($name, $alias, $of);
		return $this;
	}


	/**
	 * Enables or disables the declare(strict_types=1) statement.
	 */
	public function setStrictTypes(bool $state = true): static
	{
		$this->strictTypes = $state;
		return $this;
	}


	public function hasStrictTypes(): bool
	{
		return $this->strictTypes;
	}


	public function __toString(): string
	{
		return (new Printer)->printFile($this);
	}


	private function refreshBracketedSyntax(): void
	{
		foreach ($this->namespaces as $namespace) {
			$namespace->setBracketedSyntax(count($this->namespaces) > 1 && isset($this->namespaces['']));
		}
	}
}
