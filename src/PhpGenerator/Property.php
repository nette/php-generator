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
class Property
{
	use Nette\SmartObject;

	/** @var string */
	private $name = '';

	/** @var mixed */
	public $value;

	/** @var bool */
	private $static = FALSE;

	/** @var string  public|protected|private */
	private $visibility = 'public';

	/** @var string|NULL */
	private $comment;


	/**
	 * @return self
	 */
	public static function from(\ReflectionProperty $from)
	{
		$prop = new static($from->getName());
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->value = isset($defaults[$prop->name]) ? $defaults[$prop->name] : NULL;
		$prop->static = $from->isStatic();
		$prop->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public');
		$prop->comment = $from->getDocComment() ? preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n\t")) : NULL;
		return $prop;
	}


	/**
	 * @param  string  without $
	 */
	public function __construct($name = '')
	{
		$this->setName($name);
	}


	/** @deprecated */
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
		if (!in_array($val, ['public', 'protected', 'private'], TRUE)) {
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
	 * @param  string|NULL
	 * @return self
	 */
	public function setComment($val)
	{
		$this->comment = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getComment()
	{
		return $this->comment;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function addComment($val)
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}


	/** @deprecated */
	public function setDocuments(array $s)
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar setComment()', E_USER_DEPRECATED);
		return $this->setComment(implode("\n", $s));
	}


	/** @deprecated */
	public function getDocuments()
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar getComment()', E_USER_DEPRECATED);
		return $this->comment ? [$this->comment] : [];
	}


	/** @deprecated */
	public function addDocument($s)
	{
		trigger_error(__METHOD__ . '() is deprecated, use addComment()', E_USER_DEPRECATED);
		return $this->addComment($s);
	}

}
