<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator\Traits;


/**
 * @internal
 */
trait NameAware
{
	/** @var string */
	private $name;


	/**
	 * @param  string
	 */
	public function __construct($name)
	{
		$this->name = (string) $name;
	}


	/** @deprecated */
	public function setName($name)
	{
		trigger_error(__METHOD__ . '() is deprecated, use constructor.', E_USER_DEPRECATED);
		$this->__construct($name);
		return $this;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

}
