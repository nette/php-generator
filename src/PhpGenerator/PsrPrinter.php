<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;


/**
 * Generates PHP code following PSR-2/PSR-12/PER coding style (4-space indentation, braces on same line).
 */
class PsrPrinter extends Printer
{
	public string $indentation = '    ';
	public int $linesBetweenMethods = 1;
	public int $linesBetweenUseTypes = 1;


	protected function isBraceOnNextLine(bool $multiLine, bool $hasReturnType): bool
	{
		return !$multiLine;
	}
}
