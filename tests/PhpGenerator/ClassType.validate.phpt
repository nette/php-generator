<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$class = new ClassType('A');
	$class->setFinal(true)->setAbstract(true);
	$class->validate();
}, Nette\InvalidStateException::class, 'Class cannot be abstract and final.');

Assert::exception(function () {
	$class = new ClassType('A');
	$class->setAbstract(true)->setFinal(true);
	$class->validate();
}, Nette\InvalidStateException::class, 'Class cannot be abstract and final.');

Assert::exception(function () {
	$class = new ClassType;
	$class->setAbstract(true);
	$class->validate();
}, Nette\InvalidStateException::class, 'Anonymous class cannot be abstract or final.');
