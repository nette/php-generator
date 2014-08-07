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

	const TYPE_CLASS = "class";

	const TYPE_INTERFACE = "interface";

	const TYPE_TRAIT = "trait";

	/** @var PhpFileFragment */
	private $fragment;

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
	 * @param \ReflectionClass|string $from
	 * @return ClassType
	 */
	public static function from($from)
	{
		$from = $from instanceof \ReflectionClass ? $from : new \ReflectionClass($from);
		/** @var ClassType $class */
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


	public function __construct($name = NULL, PhpFileFragment $fragment = NULL)
	{
		$this->name = $name;
		$this->fragment = $fragment;
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


	/**
	 * @return string|string[] FQN(s)
	 */
	public function getExtends()
	{
		return $this->extends;
	}


	/**
	 * @param string|string[] $fqns
	 * @return $this
	 */
	public function setExtends($fqns)
	{
		$this->extends = (array)$fqns;

		if ($this->fragment) {
			foreach ($this->extends as $fqn) {
				$this->fragment->addUse($fqn);
			}
		}

		return $this;
	}


	/**
	 * @param string $fqn FQN
	 * @return $this
	 */
	public function addExtend($fqn)
	{
		$this->extends = (array)$this->extends;
		$this->extends[] = $fqn;

		if ($this->fragment) {
			$this->fragment->addUse($fqn);
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
	 * @param string[] $fqns
	 * @return $this
	 */
	public function setImplements($fqns)
	{
		$this->implements = (array)$fqns;

		if ($this->fragment) {
			foreach ($this->implements as $fqn) {
				$this->fragment->addUse($fqn);
			}
		}

		return $this;
	}


	/**
	 * @param string $fqn
	 * @return $this
	 */
	public function addImplement($fqn)
	{
		$this->implements = (array)$this->implements;
		$this->implements[] = $fqn;

		if ($this->fragment) {
			$this->fragment->addUse($fqn);
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
	 * @param string[] $fqns
	 * @return $this
	 */
	public function setTraits($fqns)
	{
		$this->traits = (array)$fqns;

		if ($this->fragment) {
			foreach ($this->traits as $fqn) {
				$this->fragment->addUse($fqn);
			}
		}

		return $this;
	}


	/**
	 * @param string $fqn
	 * @return $this
	 */
	public function addTrait($fqn)
	{
		$this->traits = (array)$this->traits;
		$this->traits[] = $fqn;

		if ($this->fragment) {
			$this->fragment->addUse($fqn);
		}

		return $this;
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
			$properties[] = ($property->getDocuments() ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$property->getDocuments())) . "\n */\n" : '')
				. $property->getVisibility() . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->value === NULL ? '' : ' = ' . Helpers::dump($property->value))
				. ";\n";
		}

		$extends = array();
		$implements = array();
		$traits = array();

		if ($this->fragment) {
			// relative class names are managed by fragment
			$fqnToAlias = array_flip($this->fragment->getUses());

			foreach ((array)$this->extends as $fqn) {
				$extends[] = $fqnToAlias[$fqn];
			}

			foreach ((array)$this->implements as $fqn) {
				$implements[] = $fqnToAlias[$fqn];
			}

			foreach ((array)$this->traits as $fqn) {
				$traits[] = $fqnToAlias[$fqn];
			}

		} else {
			// relative class names are managed by developer
			$extends = (array)$this->extends;
			$implements = (array)$this->implements;
			$traits = (array)$this->traits;
		}

		return Strings::normalize(
			($this->documents ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$this->documents)) . "\n */\n" : '')
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
			. "\n\n}"
		) . "\n";
	}

}
