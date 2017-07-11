<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\InvalidStateException;
use Nette\Utils\Strings;


/**
 * Namespaced part of a PHP file.
 *
 * Generates:
 * - namespace statement
 * - variable amount of use statements
 * - one or more class declarations
 */
class PhpNamespace
{
	use Nette\SmartObject;

	private static $keywords = [
		'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1,
		'callable' => 1, 'iterable' => 1, 'void' => 1, 'self' => 1, 'parent' => 1,
	];

	/** @var string */
	private $name;

	/** @var bool */
	private $bracketedSyntax = false;

	/** @var string[] */
	private $uses = [];

	/** @var ClassType[] */
	private $classes = [];


	/**
	 * @param  string|null
	 */
	public function __construct($name = null)
	{
		if ($name && !Helpers::isNamespaceIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid name.");
		}
		$this->name = (string) $name;
	}


	/** @deprecated */
	public function setName($name)
	{
		$this->__construct($name);
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name ?: null;
	}


	/**
	 * @param  bool
	 * @return static
	 * @internal
	 */
	public function setBracketedSyntax($state = true)
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
	 * @return static
	 */
	public function addUse($name, $alias = null, &$aliasOut = null)
	{
		$name = ltrim($name, '\\');
		if ($alias === null && $this->name === Helpers::extractNamespace($name)) {
			$alias = Helpers::extractShortName($name);
		}
		if ($alias === null) {
			$path = explode('\\', $name);
			$counter = null;
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
		if (isset(self::$keywords[strtolower($name)]) || $name === '') {
			return $name;
		}
		$name = ltrim($name, '\\');
		$res = null;
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
		$uses = [];
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
