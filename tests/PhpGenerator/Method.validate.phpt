<?php

declare(strict_types=1);

use Nette\PhpGenerator\Method;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$method = new Method('foo');
	$method->setFinal()->setAbstract();
	$method->validate();
}, Nette\InvalidStateException::class, 'Method foo() cannot be abstract and final or private at the same time.');

Assert::exception(function () {
	$method = new Method('foo');
	$method->setAbstract()->setFinal();
	$method->validate();
}, Nette\InvalidStateException::class, 'Method foo() cannot be abstract and final or private at the same time.');

Assert::exception(function () {
	$method = new Method('foo');
	$method->setAbstract()->setVisibility('private');
	$method->validate();
}, Nette\InvalidStateException::class, 'Method foo() cannot be abstract and final or private at the same time.');
