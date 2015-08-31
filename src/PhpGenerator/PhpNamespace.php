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
 * Namespaced part of a PHP file.
 *
 * Generates:
 * - namespace statement
 * - variable amount of use statements
 * - one or more class declarations
 */
class PhpNamespace extends Object
{
	/** @var string */
	private $name;

	/** @var bool */
	private $bracketedSyntax = FALSE;

	/** @var string[] */
	private $uses = array();

	/** @var ClassType[] */
	private $classes = array();


	public function __construct($name = NULL)
	{
		$this->setName($name);
	}


	/**
	 * @param  string|NULL
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getName()
	{
		return $this->name ?: NULL;
	}


	/**
	 * @param  bool
	 * @return self
	 * @internal
	 */
	public function setBracketedSyntax($state = TRUE)
	{
		$this->bracketedSyntax = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getBracketedSyntax()
	{
		return $this->bracketedSyntax;
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @throws InvalidStateException
	 * @return self
	 */
	public function addUse($name, $alias = NULL, &$aliasOut = NULL)
	{
		$name = ltrim($name, '\\');
		if ($alias === NULL && $this->name === Helpers::extractNamespace($name)) {
			$alias = Helpers::extractShortName($name);
		}
		if ($alias === NULL) {
			$path = explode('\\', $name);
			$counter = NULL;
			do {
				if (empty($path)) {
					$counter++;
				} else {
					$alias = array_pop($path) . $alias;
				}
			} while (isset($this->uses[$alias . $counter]) && $this->uses[$alias . $counter] !== $name);
			$alias .= $counter;

		} elseif (isset($this->uses[$alias]) && $this->uses[$alias] !== $name) {
			throw new InvalidStateException(
				"Alias '$alias' used already for '{$this->uses[$alias]}', cannot use for '{$name}'."
			);
		}

		$aliasOut = $alias;
		$this->uses[$alias] = $name;
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
	 * @param  string
	 * @return string
	 */
	public function unresolveName($name)
	{
		if (in_array(strtolower($name), array('self', 'parent', 'array', 'callable', 'string', 'bool', 'float', 'int', ''), TRUE)) {
			return $name;
		}
		$name = ltrim($name, '\\');
		$res = NULL;
		$lower = strtolower($name);
		foreach ($this->uses as $alias => $for) {
			if (Strings::startsWith($lower . '\\', strtolower($for) . '\\')) {
				$short = $alias . substr($name, strlen($for));
				if (!isset($res) || strlen($res) > strlen($short)) {
					$res = $short;
				}
			}
		}

		if (!$res && Strings::startsWith($lower, strtolower($this->name) . '\\')) {
			return substr($name, strlen($this->name) + 1);
		} else {
			return $res ?: ($this->name ? '\\' : '') . $name;
		}
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addClass($name)
	{
		if (!isset($this->classes[$name])) {
			$this->addUse($this->name . '\\' . $name);
			$this->classes[$name] = new ClassType($name, $this);
		}
		return $this->classes[$name];
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addInterface($name)
	{
		return $this->addClass($name)->setType(ClassType::TYPE_INTERFACE);
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addTrait($name)
	{
		return $this->addClass($name)->setType(ClassType::TYPE_TRAIT);
	}


	/**
	 * @return ClassType[]
	 */
	public function getClasses()
	{
		return $this->classes;
	}


	/**
	 * @return string PHP code
	 */
	public function __toString()
	{
		$uses = array();
		asort($this->uses);
		foreach ($this->uses as $alias => $name) {
			$useNamespace = Helpers::extractNamespace($name);

			if ($this->name !== $useNamespace) {
				if ($alias === $name || substr($name, -(strlen($alias) + 1)) === '\\' . $alias) {
					$uses[] = "use {$name};";
				} else {
					$uses[] = "use {$name} as {$alias};";
				}
			}
		}

		$body = ($uses ? implode("\n", $uses) . "\n\n" : '')
			. implode("\n", $this->classes);

		if ($this->bracketedSyntax) {
			return 'namespace' . ($this->name ? ' ' . $this->name : '') . " {\n\n"
				. Strings::indent($body)
				. "\n}\n";

		} else {
			return ($this->name ? "namespace {$this->name};\n\n" : '')
				. $body;
		}
	}

}
