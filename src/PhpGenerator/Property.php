<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class property description.
 */
class Property extends Member
{
	/** @var mixed */
	public $value;

	/** @var bool */
	private $static = FALSE;


	/**
	 * @return static
	 */
	public static function from(\ReflectionProperty $from)
	{
		$prop = new static($from->getName());
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->value = isset($defaults[$prop->getName()]) ? $defaults[$prop->getName()] : NULL;
		$prop->setStatic($from->isStatic());
		$prop->setVisibility($from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public'));
		$prop->setComment(Helpers::unformatDocComment($from->getDocComment()));
		return $prop;
	}


	/**
	 * @return static
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
	 * @return static
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

}
