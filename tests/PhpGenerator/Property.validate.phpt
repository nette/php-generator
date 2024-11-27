<?php

declare(strict_types=1);

use Nette\PhpGenerator\Property;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$property = new Property('a');
	$property->setFinal()->setAbstract();
	$property->validate();
}, Nette\InvalidStateException::class, 'Property $a cannot be abstract and final at the same time.');

Assert::exception(function () {
	$property = new Property('a');
	$property->setAbstract();
	$property->validate();
}, Nette\InvalidStateException::class, 'Property $a: Abstract property must have at least one abstract hook.');
