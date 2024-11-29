<?php

declare(strict_types=1);

use Nette\PhpGenerator\InterfaceType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$interface = new InterfaceType('Demo');
	$interface->addProperty('first', 123);
	$interface->validate();
}, Nette\InvalidStateException::class, 'Property Demo::$first: Interface cannot have initialized properties.');

Assert::exception(function () {
	$interface = new InterfaceType('Demo');
	$interface->addProperty('first');
	$interface->validate();
}, Nette\InvalidStateException::class, 'Property Demo::$first: Interface cannot have properties without hooks.');
