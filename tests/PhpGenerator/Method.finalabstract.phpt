<?php

/**
 * Test: Nette\PhpGenerator & variadics.
 */

declare(strict_types=1);

use Nette\PhpGenerator\Method;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$method = new Method('foo');
	$method->setFinal(true)->setAbstract(true);
}, Nette\InvalidStateException::class, 'Method cannot be final and abstract.');

Assert::exception(function () {
	$method = new Method('foo');
	$method->setAbstract(true)->setFinal(true);
}, Nette\InvalidStateException::class, 'Method cannot be final and abstract.');
