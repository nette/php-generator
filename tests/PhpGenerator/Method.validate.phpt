<?php

declare(strict_types=1);

use Nette\PhpGenerator\Method;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$method = new Method('foo');
	$method->setFinal(true)->setAbstract(true);
	$method->validate();
}, Nette\InvalidStateException::class, 'Method cannot be abstract and final or private.');

Assert::exception(function () {
	$method = new Method('foo');
	$method->setAbstract(true)->setFinal(true);
	$method->validate();
}, Nette\InvalidStateException::class, 'Method cannot be abstract and final or private.');

Assert::exception(function () {
	$method = new Method('foo');
	$method->setAbstract(true)->setVisibility('private');
	$method->validate();
}, Nette\InvalidStateException::class, 'Method cannot be abstract and final or private.');
