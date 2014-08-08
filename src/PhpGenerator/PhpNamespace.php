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
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
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
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return string[]
	 */
	public function getUses()
	{
		return $this->uses;
	}


	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @throws InvalidStateException
	 * @return self
	 */
	public function addUse($fqn, $alias = NULL, &$aliasOut = NULL)
	{
		$fqn = ltrim($fqn, '\\');
		if ($alias === NULL && $this->name === Helpers::extractNamespace($fqn)) {
			$alias = Helpers::extractShortName($fqn);
		}
		if ($alias === NULL) {
			$path = explode('\\', $fqn);
			$counter = NULL;
			do {
				if (empty($path)) {
					$counter++;
				} else {
					$alias = array_pop($path) . $alias;
				}
			} while (isset($this->uses[$alias . $counter]) && $this->uses[$alias . $counter] !== $fqn);
			$alias .= $counter;

		} elseif (isset($this->uses[$alias]) && $this->uses[$alias] !== $fqn) {
			throw new InvalidStateException(
				"Alias '$alias' used already for '{$this->uses[$alias]}', cannot use for '{$fqn}'."
			);
		}

		$aliasOut = $alias;
		$this->uses[$alias] = $fqn;
		return $this;
	}


	/**
	 * @param  string
	 * @return string
	 */
	public function unresolveName($name)
	{
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
			return $res ?: '\\' . $name;
		}
	}


	/**
	 * @return ClassType[]
	 */
	public function getClasses()
	{
		return $this->classes;
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
	 * @return string PHP code
	 */
	public function __toString()
	{
		$uses = array();
		asort($this->uses);
		foreach ($this->uses as $alias => $fqn) {
			$useNamespace = Helpers::extractNamespace($fqn);

			if ($this->name !== $useNamespace) {
				if ($alias === $fqn || substr($fqn, -(strlen($alias) + 1)) === '\\' . $alias) {
					$uses[] = "use {$fqn};";
				} else {
					$uses[] = "use {$fqn} as {$alias};";
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
