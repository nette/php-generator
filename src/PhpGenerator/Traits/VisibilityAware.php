<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator\Traits;

use Nette;


/**
 * @internal
 */
trait VisibilityAware
{
	/** @var string|null  public|protected|private */
	private $visibility;


	/**
	 * @param  string|null  public|protected|private
	 * @return static
	 */
	public function setVisibility($val)
	{
		if (!in_array($val, ['public', 'protected', 'private', null], true)) {
			throw new Nette\InvalidArgumentException('Argument must be public|protected|private.');
		}
		$this->visibility = $val;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}
}
