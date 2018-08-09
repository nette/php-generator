<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;

use Nette;
use Nette\PhpGenerator\ClassType;


/**
 * @internal
 */
trait VisibilityAware
{
	/** @var string|null  public|protected|private */
	private $visibility;


	/**
	 * @param  string|null  $val  public|protected|private
	 * @return static
	 */
	public function setVisibility(?string $val): self
	{
		if (!in_array($val, [ClassType::VISIBILITY_PUBLIC, ClassType::VISIBILITY_PROTECTED, ClassType::VISIBILITY_PRIVATE, null], true)) {
			throw new Nette\InvalidArgumentException('Argument must be public|protected|private.');
		}
		$this->visibility = $val;
		return $this;
	}


	public function getVisibility(): ?string
	{
		return $this->visibility;
	}
}
