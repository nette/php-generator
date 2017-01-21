<?php

/**
 * Test: Nette\PhpGenerator\Helpers::dump()
 * @phpversion 7
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	Helpers::dump(new class {});
}, Nette\InvalidArgumentException::class, 'Cannot dump anonymous class.');
