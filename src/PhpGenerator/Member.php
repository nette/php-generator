<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class member.
 */
abstract class Member
{
	use Nette\SmartObject;

	/** @var string */
	private $name;

	/** @var string|NULL  public|protected|private */
	private $visibility;

	/** @var string|NULL */
	private $comment;


	/**
	 * @param  string
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
	 * @param  string|NULL  public|protected|private
	 * @return static
	 */
	public function setVisibility($val)
	{
		if (!in_array($val, ['public', 'protected', 'private', NULL], TRUE)) {
			throw new Nette\InvalidArgumentException('Argument must be public|protected|private.');
		}
		$this->visibility = $val;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}


	/**
	 * @param  string|NULL
	 * @return static
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
	 * @return static
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
