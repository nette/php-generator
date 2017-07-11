<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator\Traits;


/**
 * @internal
 */
trait CommentAware
{
	/** @var string|null */
	private $comment;


	/**
	 * @param  string|null
	 * @return static
	 */
	public function setComment($val)
	{
		$this->comment = $val ? (string) $val : null;
		return $this;
	}


	/**
	 * @return string|null
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
