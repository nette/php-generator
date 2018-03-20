<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;


/**
 * Class/Interface/Trait description.
 *
 * @property Method[] $methods
 * @property Property[] $properties
 */
class ClassType
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	const TYPE_CLASS = 'class';

	const TYPE_INTERFACE = 'interface';

	const TYPE_TRAIT = 'trait';

	/** @var PhpNamespace|null */
	private $namespace;

	/** @var string|null */
	private $name;

	/** @var string  class|interface|trait */
	private $type = 'class';

	/** @var bool */
	private $final = false;

	/** @var bool */
	private $abstract = false;

	/** @var string|string[] */
	private $extends = [];

	/** @var string[] */
	private $implements = [];

	/** @var array[] */
	private $traits = [];

	/** @var Constant[] name => Constant */
	private $consts = [];

	/** @var Property[] name => Property */
	private $properties = [];

	/** @var Method[] name => Method */
	private $methods = [];


	/**
	 * @param  string|object
	 * @return static
	 */
	public static function from($class)
	{
		return (new Factory)->fromClassReflection(
			$class instanceof \ReflectionClass ? $class : new \ReflectionClass($class)
		);
	}


	/**
	 * @param  string|null
	 */
	public function __construct($name = null, PhpNamespace $namespace = null)
	{
		$this->setName($name);
		$this->namespace = $namespace;
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$traits = [];
		foreach ($this->traits as $trait => $resolutions) {
			$traits[] = 'use ' . ($this->namespace ? $this->namespace->unresolveName($trait) : $trait)
				. ($resolutions ? " {\n\t" . implode(";\n\t", $resolutions) . ";\n}" : ';');
		}

		$consts = [];
		foreach ($this->consts as $const) {
			$consts[] = Helpers::formatDocComment($const->getComment())
				. ($const->getVisibility() ? $const->getVisibility() . ' ' : '')
				. 'const ' . $const->getName() . ' = ' . Helpers::dump($const->getValue()) . ';';
		}

		$properties = [];
		foreach ($this->properties as $property) {
			$properties[] = Helpers::formatDocComment($property->getComment())
				. ($property->getVisibility() ?: 'public') . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->value === null ? '' : ' = ' . Helpers::dump($property->value))
				. ';';
		}

		$mapper = function (array $arr) {
			return $this->namespace ? array_map([$this->namespace, 'unresolveName'], $arr) : $arr;
		};

		return Strings::normalize(
			Helpers::formatDocComment($this->comment . "\n")
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->name ? "$this->type $this->name " : '')
			. ($this->extends ? 'extends ' . implode(', ', $mapper((array) $this->extends)) . ' ' : '')
			. ($this->implements ? 'implements ' . implode(', ', $mapper($this->implements)) . ' ' : '')
			. ($this->name ? "\n" : '') . "{\n"
			. Strings::indent(
				($this->traits ? implode("\n", $traits) . "\n\n" : '')
				. ($this->consts ? implode("\n", $consts) . "\n\n" : '')
				. ($this->properties ? implode("\n\n", $properties) . "\n\n\n" : '')
				. ($this->methods ? implode("\n\n\n", $this->methods) . "\n" : ''), 1)
			. '}'
		) . ($this->name ? "\n" : '');
	}


	/**
	 * @return PhpNamespace|null
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @param  string|null
	 * @return static
	 */
	public function setName($name)
	{
		if ($name !== null && !Helpers::isIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
		}
		$this->name = $name;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  string
	 * @return static
	 */
	public function setType($type)
	{
		if (!in_array($type, ['class', 'interface', 'trait'], true)) {
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
	 * @return static
	 */
	public function setFinal($state = true)
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
	 * @return static
	 */
	public function setAbstract($state = true)
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
	 * @return static
	 */
	public function setExtends($names)
	{
		if (!is_string($names) && !is_array($names)) {
			throw new Nette\InvalidArgumentException('Argument must be string or string[].');
		}
		$this->validate((array) $names);
		$this->extends = $names;
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
	 * @return static
	 */
	public function addExtend($name)
	{
		$this->validate([$name]);
		$this->extends = (array) $this->extends;
		$this->extends[] = $name;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return static
	 */
	public function setImplements(array $names)
	{
		$this->validate($names);
		$this->implements = $names;
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
	 * @return static
	 */
	public function addImplement($name)
	{
		$this->validate([$name]);
		$this->implements[] = $name;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return static
	 */
	public function setTraits(array $names)
	{
		$this->validate($names);
		$this->traits = array_fill_keys($names, []);
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getTraits()
	{
		return array_keys($this->traits);
	}


	/**
	 * @param  string
	 * @return static
	 */
	public function addTrait($name, array $resolutions = [])
	{
		$this->validate([$name]);
		$this->traits[$name] = $resolutions;
		return $this;
	}


	/**
	 * @deprecated  use setConstants()
	 * @return static
	 */
	public function setConsts(array $consts)
	{
		return $this->setConstants($consts);
	}


	/**
	 * @deprecated  use getConstants()
	 * @return array
	 */
	public function getConsts()
	{
		return array_map(function ($const) { return $const->getValue(); }, $this->consts);
	}


	/**
	 * @deprecated  use addConstant()
	 * @param  string
	 * @param  mixed
	 * @return static
	 */
	public function addConst($name, $value)
	{
		$this->addConstant($name, $value);
		return $this;
	}


	/**
	 * @param  Constant[]|mixed[]
	 * @return static
	 */
	public function setConstants(array $consts)
	{
		$this->consts = [];
		foreach ($consts as $k => $v) {
			$const = $v instanceof Constant ? $v : (new Constant($k))->setValue($v);
			$this->consts[$const->getName()] = $const;
		}
		return $this;
	}


	/**
	 * @return Constant[]
	 */
	public function getConstants()
	{
		return $this->consts;
	}


	/**
	 * @param  string
	 * @param  mixed
	 * @return Constant
	 */
	public function addConstant($name, $value)
	{
		return $this->consts[$name] = (new Constant($name))->setValue($value);
	}


	/**
	 * @param  Property[]
	 * @return static
	 */
	public function setProperties(array $props)
	{
		$this->properties = [];
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
	public function addProperty($name, $value = null)
	{
		return $this->properties[$name] = (new Property($name))->setValue($value);
	}


	/**
	 * @param  Method[]
	 * @return static
	 */
	public function setMethods(array $methods)
	{
		$this->methods = [];
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
		$method = (new Method($name))->setNamespace($this->namespace);
		if ($this->type === 'interface') {
			$method->setBody(false);
		} else {
			$method->setVisibility('public');
		}
		return $this->methods[$name] = $method;
	}


	private function validate(array $names)
	{
		foreach ($names as $name) {
			if (!Helpers::isNamespaceIdentifier($name, true)) {
				throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
			}
		}
	}
}
