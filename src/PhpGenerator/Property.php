<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class property description.
 *
 * @author     David Grudl
 */
class Property extends Nette\Object
{
	/** @var string */
	private $name;

	/** @var mixed */
	public $value;

	/** @var bool */
	private $static = FALSE;

	/** @var string  public|protected|private */
	private $visibility = 'public';

	/** @var array of string */
	private $documents = array();


	/**
	 * @return self
	 */
	public static function from(\ReflectionProperty $from)
	{
		$prop = new static;
		$prop->name = $from->getName();
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->value = isset($defaults[$prop->name]) ? $defaults[$prop->name] : NULL;
		$prop->static = $from->isStatic();
		$prop->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public');
		$prop->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n\t"));
		return $prop;
	}


	/**
	 * @param  string  without $
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
	 * @return self
	 */
	public function setValue($val)
	{
		$this->value = $val;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * @param  bool
	 * @return self
	 */
	public function setStatic($state = TRUE)
	{
		$this->static = (bool) $state;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->static;
	}


	/**
	 * @param  string  public|protected|private
	 * @return self
	 */
	public function setVisibility($val)
	{
		if (!in_array($val, array('public', 'protected', 'private'), TRUE)) {
			throw new Nette\InvalidArgumentException('Argument must be public|protected|private.');
		}
		$this->visibility = (string) $val;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getVisibility()
	{
		return $this->visibility;
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

}
