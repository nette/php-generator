<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette,
	Nette\Utils\Strings;


/**
 * Class/Interface/Trait description.
 *
 * @author     David Grudl
 *
 * @method PhpNamespace getNamespace()
 * @method ClassType setName(string)
 * @method string getName()
 * @method ClassType setType(string)
 * @method string getType()
 * @method ClassType setFinal(bool)
 * @method bool isFinal()
 * @method ClassType setAbstract(bool)
 * @method bool isAbstract()
 * @method ClassType setExtends(string[]|string)
 * @method string[]|string getExtends()
 * @method ClassType addExtend(string)
 * @method ClassType setImplements(string[])
 * @method string[] getImplements()
 * @method ClassType addImplement(string)
 * @method ClassType setTraits(string[])
 * @method string[] getTraits()
 * @method ClassType addTrait(string)
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
	const TYPE_CLASS = 'class';

	const TYPE_INTERFACE = 'interface';

	const TYPE_TRAIT = 'trait';

	/** @var PhpNamespace */
	private $namespace;

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
	 * @param  \ReflectionClass|string
	 * @return ClassType
	 */
	public static function from($from)
	{
		$from = $from instanceof \ReflectionClass ? $from : new \ReflectionClass($from);
		$class = new static($from->getShortName());
		$class->type = $from->isInterface() ? 'interface' : (PHP_VERSION_ID >= 50400 && $from->isTrait() ? 'trait' : 'class');
		$class->final = $from->isFinal();
		$class->abstract = $from->isAbstract() && $class->type === 'class';
		$class->implements = $from->getInterfaceNames();
		$class->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n"));
		$namespace = $from->getNamespaceName();
		if ($from->getParentClass()) {
			$class->extends = $from->getParentClass()->getName();
			if ($namespace) {
				$class->extends = Strings::startsWith($class->extends, "$namespace\\") ? substr($class->extends, strlen($namespace) + 1) : '\\' . $class->extends;
			}
			$class->implements = array_diff($class->implements, $from->getParentClass()->getInterfaceNames());
		}
		if ($namespace) {
			foreach ($class->implements as & $interface) {
				$interface = Strings::startsWith($interface, "$namespace\\") ? substr($interface, strlen($namespace) + 1) : '\\' . $interface;
			}
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


	public function __construct($name = NULL, PhpNamespace $namespace = NULL)
	{
		$this->name = $name;
		$this->namespace = $namespace;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return ClassType
	 */
	public function addConst($name, $value)
	{
		$this->consts[$name] = $value;
		return $this;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return Property
	 */
	public function addProperty($name, $value = NULL)
	{
		$property = new Property;
		return $this->properties[$name] = $property->setName($name)->setValue($value);
	}


	/**
	 * @param  string
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


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$consts = array();
		foreach ($this->consts as $name => $value) {
			$consts[] = "const $name = " . Helpers::dump($value) . ";\n";
		}

		$properties = array();
		foreach ($this->properties as $property) {
			$properties[] = ($property->getDocuments() ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array) $property->getDocuments())) . "\n */\n" : '')
				. $property->getVisibility() . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->value === NULL ? '' : ' = ' . Helpers::dump($property->value))
				. ";\n";
		}

		$extends = $implements = $traits = array();
		if ($this->namespace) {
			foreach ((array) $this->extends as $name) {
				$extends[] = $this->namespace->unresolveName($name);
			}

			foreach ((array) $this->implements as $name) {
				$implements[] = $this->namespace->unresolveName($name);
			}

			foreach ((array) $this->traits as $name) {
				$traits[] = $this->namespace->unresolveName($name);
			}

		} else {
			$extends = (array) $this->extends;
			$implements = (array) $this->implements;
			$traits = (array) $this->traits;
		}

		foreach ($this->methods as $method) {
			$method->setNamespace($this->namespace);
		}

		return Strings::normalize(
			($this->documents ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array) $this->documents)) . "\n */\n" : '')
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. $this->type . ' '
			. $this->name . ' '
			. ($this->extends ? 'extends ' . implode(', ', $extends) . ' ' : '')
			. ($this->implements ? 'implements ' . implode(', ', $implements) . ' ' : '')
			. "\n{\n\n"
			. Strings::indent(
				($this->traits ? 'use ' . implode(', ', $traits) . ";\n\n" : '')
				. ($this->consts ? implode('', $consts) . "\n\n" : '')
				. ($this->properties ? implode("\n", $properties) . "\n\n" : '')
				. implode("\n\n\n", $this->methods), 1)
			. "\n\n}"
		) . "\n";
	}

}
