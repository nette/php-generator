<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;


/**
 * Class/Interface/Trait description.
 *
 * @author     David Grudl
 *
 * @method ClassType setName(string)
 * @method string getName()
 * @method ClassType setType(string)
 * @method string getType()
 * @method ClassType setFinal(bool)
 * @method bool isFinal()
 * @method ClassType setAbstract(bool)
 * @method bool isAbstract()
 * @method ClassType setDocuments(string[])
 * @method string[] getDocuments()
 * @method ClassType addDocument(string)
 * @method ClassType setConsts(scalar[])
 * @method scalar[] getConsts()
 * @method ClassType setProperties(Property[])
 * @method Property[] getProperties()
 * @method ClassType setMethods(Method[])
 * @method Method[] getMethods()
 */
class ClassType extends Nette\Object
{
	/** @var string */
	private $namespace;

	/** @var string[] */
	private $uses = array();

	/** @var string */
	private $name;

	/** @var string  class|interface|trait */
	private $type = 'class';

	/** @var bool */
	private $final;

	/** @var bool */
	private $abstract;

	/** @var string[]|string */
	private $extends = array();

	/** @var string[] */
	private $implements = array();

	/** @var string[] */
	private $traits = array();

	/** @var string[] */
	private $documents = array();

	/** @var mixed[] name => value */
	private $consts = array();

	/** @var Property[] name => Property */
	private $properties = array();

	/** @var Method[] name => Method */
	private $methods = array();


	/**
	 * @param string|\ReflectionClass $from
	 * @return ClassType
	 */
	public static function from($from)
	{
		$from = $from instanceof \ReflectionClass ? $from : new \ReflectionClass($from);
		/** @var ClassType $class */
		$class = new static($from->getShortName());
		$class->setNamespace($from->getNamespaceName());
		$class->setType($from->isInterface() ? 'interface' : (PHP_VERSION_ID >= 50400 && $from->isTrait() ? 'trait' : 'class'));
		$class->setFinal($from->isFinal());
		$class->setAbstract($from->isAbstract() && $class->type === 'class');
		$class->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n"));

		$implements = $from->getInterfaceNames();

		if ($from->getParentClass()) {
			$class->setExtends($from->getParentClass()->getName());
			$implements = array_diff($implements, $from->getParentClass()->getInterfaceNames());
		}

		if (!empty($implements)) {
			$class->setImplements($implements);
		}

		foreach ($from->getProperties() as $prop) {
			if ($prop->getDeclaringClass() == $from) { // intentionally ==
				$class->properties[$prop->getName()] = Property::from($prop);
			}
		}
		foreach ($from->getMethods() as $method) {
			if ($method->getDeclaringClass() == $from) { // intentionally ==
				$class->methods[$method->getName()] = Method::from($method);
			}
		}
		return $class;
	}


	public function __construct($name = NULL)
	{
		$this->setNamespace(Helpers::extractNamespace($name));
		$this->setName(Helpers::extractShortName($name));
	}

	/**
	 * @param string $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		if (empty($namespace)) {
			$namespace = NULL;
		}

		$this->namespace = $namespace;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @param string[] $uses
	 * @return $this
	 */
	public function setUses($uses)
	{
		foreach ($uses as $use) {
			$this->addUse($use);
		}

		return $this;
	}

	/**
	 * @return \string[]
	 */
	public function getUses()
	{
		return $this->uses;
	}

	/**
	 * @param string $fqn
	 * @param string $alias
	 * @param string $aliasOut returns generated alias through this parameter
	 * @throws \Nette\InvalidStateException
	 * @return ClassType
	 */
	public function addUse($fqn, $alias = NULL, &$aliasOut = NULL)
	{
		if ($alias === NULL) {
			$path = explode("\\", $fqn);

			do {
				$alias = array_pop($path) . $alias;
			} while (!empty($path) && isset($this->uses[$alias]));

			if (empty($path) && isset($this->uses[$alias])) {
				throw new Nette\InvalidStateException(
					"Could not determine alias for '{$fqn}'."
				);
			}
		}

		if (isset($this->uses[$alias]) && $this->uses[$alias] !== $fqn) {
			throw new Nette\InvalidStateException(
				"Alias '$alias' used already for '{$this->uses[$alias]}', cannot use for '{$fqn}'."
			);
		}

		$aliasOut = $alias;
		$this->uses[$alias] = $fqn;

		return $this;
	}

	/**
	 * @param string|string[] $fqns
	 * @return $this
	 */
	public function setExtends($fqns)
	{
		$this->extends = (array)$fqns;

		foreach ($this->extends as $fqn) {
			$this->addUse($fqn);
		}

		return $this;
	}

	/**
	 * @return string|string[] FQN(s)
	 */
	public function getExtends()
	{
		return $this->extends;
	}

	/**
	 * @param string $fqn FQN
	 * @return $this
	 */
	public function addExtend($fqn)
	{
		$this->extends = (array)$this->extends;
		$this->extends[] = $fqn;

		$this->addUse($fqn);

		return $this;
	}

	/**
	 * @param string[] $fqns
	 * @return $this
	 */
	public function setImplements($fqns)
	{
		$this->implements = (array)$fqns;

		foreach ($this->implements as $fqn) {
			$this->addUse($fqn);
		}

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getImplements()
	{
		return $this->implements;
	}

	/**
	 * @param string $fqn
	 * @return $this
	 */
	public function addImplement($fqn)
	{
		$this->implements = (array)$this->implements;
		$this->implements[] = $fqn;

		$this->addUse($fqn);

		return $this;
	}

	/**
	 * @param string[] $fqns
	 * @return $this
	 */
	public function setTraits($fqns)
	{
		$this->traits = (array)$fqns;

		foreach ($this->traits as $fqn) {
			$this->addUse($fqn);
		}

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getTraits()
	{
		return $this->traits;
	}

	/**
	 * @param string $fqn
	 * @return $this
	 */
	public function addTrait($fqn)
	{
		$this->traits = (array)$this->traits;
		$this->traits[] = $fqn;

		$this->addUse($fqn);

		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return ClassType
	 */
	public function addConst($name, $value)
	{
		$this->consts[$name] = $value;
		return $this;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return Property
	 */
	public function addProperty($name, $value = NULL)
	{
		$property = new Property;
		return $this->properties[$name] = $property->setName($name)->setValue($value);
	}


	/**
	 * @param string $name
	 * @return Method
	 */
	public function addMethod($name)
	{
		$method = new Method;
		if ($this->type === 'interface') {
			$method->setVisibility('')->setBody(FALSE);
		} else {
			$method->setVisibility('public');
		}
		return $this->methods[$name] = $method->setName($name);
	}


	/** @return string  PHP code */
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

		$fqnToAlias = array_flip($this->uses);

		$extends = array();
		foreach ((array)$this->extends as $fqn) {
			$extends[] = $fqnToAlias[$fqn];
		}

		$implements = array();
		foreach ((array)$this->implements as $fqn) {
			$implements[] = $fqnToAlias[$fqn];
		}

		$traits = array();
		foreach ((array)$this->traits as $fqn) {
			$traits[] = $fqnToAlias[$fqn];
		}

		$consts = array();
		foreach ($this->consts as $name => $value) {
			$consts[] = "const $name = " . Helpers::dump($value) . ";\n";
		}

		$properties = array();
		foreach ($this->properties as $property) {
			/** @var Property $property */
			$properties[] = ($property->getDocuments() ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$property->getDocuments())) . "\n */\n" : '')
				. $property->getVisibility() . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->value === NULL ? '' : ' = ' . Helpers::dump($property->value))
				. ";\n";
		}

		return Strings::normalize(
			(empty($this->namespace) ? "" : "namespace " . $this->namespace . ";\n\n")
			. (empty($uses) ? "" : implode("\n", $uses) . "\n\n")
			. ($this->documents ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$this->documents)) . "\n */\n" : '')
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. $this->type . ' '
			. $this->name . ' '
			. ($this->extends ? 'extends ' . implode(', ', $extends) . ' ' : '')
			. ($this->implements ? 'implements ' . implode(', ', $implements) . ' ' : '')
			. "\n{\n\n"
			. Strings::indent(
				($this->traits ? "use " . implode(', ', $traits) . ";\n\n" : '')
				. ($this->consts ? implode('', $consts) . "\n\n" : '')
				. ($this->properties ? implode("\n", $properties) . "\n\n" : '')
				. implode("\n\n\n", $this->methods), 1)
			. "\n\n}") . "\n";
	}

}
