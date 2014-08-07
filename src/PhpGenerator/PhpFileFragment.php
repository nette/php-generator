<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette\InvalidStateException;
use Nette\Object;
use Nette\Utils\Strings;

/**
 * Namespaced fragment of a PHP file
 *
 * Generates:
 *
 * - namespace statement
 * - variable amount of use statements
 * - one or more class declarations
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class PhpFileFragment extends Object
{

	/** @var string */
	private $namespace;

	/** @var boolean */
	private $bracketedNamespaceSyntax = FALSE;

	/** @var string[] */
	private $uses = array();

	/** @var ClassType[] */
	private $classTypes = array();

	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}


	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @param string $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}


	/**
	 * @return boolean
	 */
	public function getBracketedNamespaceSyntax()
	{
		return $this->bracketedNamespaceSyntax;
	}


	/**
	 * @param boolean $bracketedNamespaceSyntax
	 * @return $this
	 * @internal
	 */
	public function setBracketedNamespaceSyntax($bracketedNamespaceSyntax)
	{
		$this->bracketedNamespaceSyntax = $bracketedNamespaceSyntax;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getUses()
	{
		return $this->uses;
	}


	/**
	 * @param string[] $uses
	 * @return $this
	 */
	public function setUses($uses)
	{
		$this->uses = $uses;
		return $this;
	}


	/**
	 * @param string $fqn
	 * @param string $alias
	 * @param string $aliasOut
	 * @throws InvalidStateException
	 * @return $this
	 */
	public function addUse($fqn, $alias = NULL, &$aliasOut = NULL)
	{
		$fqn = trim($fqn, "\\");
		$existingAlias = array_search($fqn, $this->uses);

		if ($existingAlias !== FALSE) {
			if ($alias !== NULL && $existingAlias !== $alias) {
				throw new InvalidStateException(
					"'{$fqn}' already aliased to '{$existingAlias}', cannot alias to '{$alias}'."
				);
			}

			$aliasOut = $existingAlias;

		} else {
			if ($alias === NULL) {
				$path = explode("\\", $fqn);

				do {
					$alias = array_pop($path) . $alias;
				} while (!empty($path) && isset($this->uses[$alias]));

				if (empty($path) && isset($this->uses[$alias])) {
					throw new InvalidStateException(
						"Could not determine alias for '{$fqn}'."
					);
				}
			}

			if (isset($this->uses[$alias]) && $this->uses[$alias] !== $fqn) {
				throw new InvalidStateException(
					"Alias '$alias' used already for '{$this->uses[$alias]}', cannot use for '{$fqn}'."
				);
			}

			$aliasOut = $alias;
			$this->uses[$alias] = $fqn;
		}

		return $this;
	}


	/**
	 * @return ClassType[]
	 */
	public function getClassTypes()
	{
		return $this->classTypes;
	}


	/**
	 * @param ClassType[] $classTypes
	 * @return $this
	 */
	public function setClassTypes($classTypes)
	{
		$this->classTypes = $classTypes;
		return $this;
	}


	/**
	 * @param string $name
	 * @return ClassType
	 */
	public function addClassType($name)
	{
		if (!isset($this->classTypes[$name])) {
			$this->addUse($this->namespace . "\\" . $name);
			$this->classTypes[$name] = new ClassType($name, $this);
		}

		return $this->classTypes[$name];
	}


	/**
	 * @param string $name
	 * @return ClassType
	 */
	public function addClass($name)
	{
		return $this->addClassType($name)->setType(ClassType::TYPE_CLASS);
	}


	/**
	 * @param string $name
	 * @return ClassType
	 */
	public function addInterface($name)
	{
		return $this->addClassType($name)->setType(ClassType::TYPE_INTERFACE);
	}


	/**
	 * @param string $name
	 * @return ClassType
	 */
	public function addTrait($name)
	{
		return $this->addClassType($name)->setType(ClassType::TYPE_TRAIT);
	}


	/**
	 * @return string PHP code
	 */
	public function __toString()
	{
		$uses = array();
		asort($this->uses);
		foreach ($this->uses as $alias => $fqn) {
			$useNamespace = Helpers::extractNamespace($fqn);

			if ($this->namespace !== $useNamespace) {
				if ($alias === $fqn || substr($fqn, -(strlen($alias) + 1)) === "\\" . $alias) {
					$uses[] = "use {$fqn};";
				} else {
					$uses[] = "use {$fqn} as {$alias};";
				}
			}
		}

		$fragmentBody =
			(empty($uses) ? "" : implode("\n", $uses) . "\n\n") .
			(empty($this->classTypes) ? "" : implode("\n", array_map(function (ClassType $classType) {
				return (string)$classType;
			}, $this->classTypes)));

		if ($this->bracketedNamespaceSyntax) {
			return Strings::normalize(
				"namespace" . (empty($this->namespace) ? "" : " " . $this->namespace) . " {\n\n" .
				Strings::indent($fragmentBody) .
				"\n}\n"
			);

		} else {
			return Strings::normalize(
				(empty($this->namespace) ? "" : "namespace {$this->namespace};\n\n") .
				$fragmentBody
			);
		}
	}

}
