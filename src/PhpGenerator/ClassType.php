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
 */
class ClassType extends Nette\Object
{
	const TYPE_CLASS = 'class';

	const TYPE_INTERFACE = 'interface';

	const TYPE_TRAIT = 'trait';

	/** @var PhpNamespace|NULL */
	private $namespace;

	/** @var string */
	private $name;

	/** @var string  class|interface|trait */
	private $type = 'class';

	/** @var bool */
	private $final = FALSE;

	/** @var bool */
	private $abstract = FALSE;

	/** @var string|string[] */
	private $extends = array();

	/** @var string[] */
	private $implements = array();

	/** @var string[] */
	private $traits = array();

	/** @var string[] */
	private $documents = array();

	/** @var array name => value */
	private $consts = array();

	/** @var Property[] name => Property */
	private $properties = array();

	/** @var Method[] name => Method */
	private $methods = array();


	/**
	 * @param  \ReflectionClass|string
	 * @return self
	 */
	public static function from($from)
	{
		$from = $from instanceof \ReflectionClass ? $from : new \ReflectionClass($from);
		$class = new static($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
		$class->type = $from->isInterface() ? 'interface' : (PHP_VERSION_ID >= 50400 && $from->isTrait() ? 'trait' : 'class');
		$class->final = $from->isFinal() && $class->type === 'class';
		$class->abstract = $from->isAbstract() && $class->type === 'class';
		$class->implements = $from->getInterfaceNames();
		$class->documents = $from->getDocComment() ? array(preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n\t"))) : array();
		if ($from->getParentClass()) {
			$class->extends = $from->getParentClass()->getName();
			$class->implements = array_diff($class->implements, $from->getParentClass()->getInterfaceNames());
		}
		foreach ($from->getProperties() as $prop) {
			if ($prop->getDeclaringClass() == $from) { // intentionally ==
				$class->properties[$prop->getName()] = Property::from($prop);
			}
		}
		foreach ($from->getMethods() as $method) {
			if ($method->getDeclaringClass() == $from) { // intentionally ==
				$class->methods[$method->getName()] = Method::from($method)->setNamespace($class->namespace);
			}
		}
		return $class;
	}


	public function __construct($name = '', PhpNamespace $namespace = NULL)
	{
		$this->setName($name);
		$this->namespace = $namespace;
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
			$doc = str_replace("\n", "\n * ", implode("\n", $property->getDocuments()));
			$properties[] = ($property->getDocuments() ? (strpos($doc, "\n") === FALSE ? "/** $doc */\n" : "/**\n * $doc\n */\n") : '')
				. $property->getVisibility() . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->value === NULL ? '' : ' = ' . Helpers::dump($property->value))
				. ";\n";
		}

		$namespace = $this->namespace ?: new PhpNamespace;
		$mapper = function (array $arr) use ($namespace) {
			return array_map(array($namespace, 'unresolveName'), $arr);
		};

		return Strings::normalize(
			($this->documents ? str_replace("\n", "\n * ", "/**\n" . implode("\n", $this->documents)) . "\n */\n" : '')
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. $this->type . ' '
			. $this->name . ' '
			. ($this->extends ? 'extends ' . implode(', ', $mapper((array) $this->extends)) . ' ' : '')
			. ($this->implements ? 'implements ' . implode(', ', $mapper($this->implements)) . ' ' : '')
			. "\n{\n"
			. Strings::indent(
				($this->traits ? 'use ' . implode(";\nuse ", $mapper($this->traits)) . ";\n\n" : '')
				. ($this->consts ? implode('', $consts) . "\n" : '')
				. ($this->properties ? implode("\n", $properties) . "\n" : '')
				. ($this->methods ? "\n" . implode("\n\n\n", $this->methods) . "\n\n" : ''), 1)
			. '}'
		) . "\n";
	}


	/**
	 * @return PhpNamespace|NULL
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
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
	public function setType($type)
	{
		if (!in_array($type, array('class', 'interface', 'trait'), TRUE)) {
			throw new Nette\InvalidArgumentException('Argument must be class|interface|trait.');
		}
		$this->type = $type;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param  bool
	 * @return self
	 */
	public function setFinal($state = TRUE)
	{
		$this->final = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->final;
	}


	/**
	 * @param  bool
	 * @return self
	 */
	public function setAbstract($state = TRUE)
	{
		$this->abstract = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return $this->abstract;
	}


	/**
	 * @param  string|string[]
	 * @return self
	 */
	public function setExtends($types)
	{
		if (!is_string($types) && !(is_array($types) && array_filter($types, 'is_string') === $types)) {
			throw new Nette\InvalidArgumentException('Argument must be string or string[].');
		}
		$this->extends = $types;
		return $this;
	}


	/**
	 * @return string|string[]
	 */
	public function getExtends()
	{
		return $this->extends;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function addExtend($type)
	{
		$this->extends = (array) $this->extends;
		$this->extends[] = (string) $type;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return self
	 */
	public function setImplements(array $types)
	{
		$this->implements = $types;
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
	 * @param  string
	 * @return self
	 */
	public function addImplement($type)
	{
		$this->implements[] = (string) $type;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return self
	 */
	public function setTraits(array $traits)
	{
		$this->traits = $traits;
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
	 * @param  string
	 * @return self
	 */
	public function addTrait($trait)
	{
		$this->traits[] = (string) $trait;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return self
	 */
	public function setDocuments(array $s)
	{
		$this->documents = $s;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getDocuments()
	{
		return $this->documents;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function addDocument($s)
	{
		$this->documents[] = (string) $s;
		return $this;
	}


	/**
	 * @return self
	 */
	public function setConsts(array $consts)
	{
		$this->consts = $consts;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getConsts()
	{
		return $this->consts;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return self
	 */
	public function addConst($name, $value)
	{
		$this->consts[$name] = $value;
		return $this;
	}


	/**
	 * @param  Property[]
	 * @return self
	 */
	public function setProperties(array $props)
	{
		$this->properties = array();
		foreach ($props as $v) {
			if (!$v instanceof Property) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Property[].');
			}
			$this->properties[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Property[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}


	/**
	 * @return Property
	 */
	public function getProperty($name)
	{
		if (!isset($this->properties[$name])) {
			throw new Nette\InvalidArgumentException("Property '$name' not found.");
		}
		return $this->properties[$name];
	}


	/**
	 * @param  string  without $
	 * @param  mixed
	 * @return Property
	 */
	public function addProperty($name, $value = NULL)
	{
		$property = new Property($name);
		return $this->properties[$name] = $property->setValue($value);
	}


	/**
	 * @param  Method[]
	 * @return self
	 */
	public function setMethods(array $methods)
	{
		$this->methods = array();
		foreach ($methods as $v) {
			if (!$v instanceof Method) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Method[].');
			}
			$this->methods[$v->getName()] = $v->setNamespace($this->namespace);
		}
		return $this;
	}


	/**
	 * @return Method[]
	 */
	public function getMethods()
	{
		return $this->methods;
	}


	/**
	 * @return Method
	 */
	public function getMethod($name)
	{
		if (!isset($this->methods[$name])) {
			throw new Nette\InvalidArgumentException("Method '$name' not found.");
		}
		return $this->methods[$name];
	}


	/**
	 * @param  string
	 * @return Method
	 */
	public function addMethod($name)
	{
		$method = new Method($name);
		if ($this->type === 'interface') {
			$method->setVisibility(NULL)->setBody(FALSE);
		} else {
			$method->setVisibility('public');
		}
		return $this->methods[$name] = $method->setNamespace($this->namespace);
	}

}
