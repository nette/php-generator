<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;


/**
 * fn () => ...
 *
 * @property string $body
 */
final class ArrowFunction
{
	use Nette\SmartObject;
	use Traits\FunctionLike;

	public function __toString(): string
	{
		try {
			return (new Printer)->printArrowFunction($this);
		} catch (\Throwable $e) {
			if (PHP_VERSION_ID >= 70400) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}
}
