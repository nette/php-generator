<?php declare(strict_types=1);

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

Assert::exception(function () {
	$property = new Property('a');
	$property->setType('int')->setReadOnly()->setStatic();
	$property->validate();
}, Nette\InvalidStateException::class, 'Property $a cannot be static and readonly at the same time.');

Assert::exception(function () {
	$property = new Property('a');
	$property->setType('int')->setReadOnly()->setValue(5);
	$property->validate();
}, Nette\InvalidStateException::class, 'Property $a: Read-only property cannot have a default value.');

// readonly property without a default value is fine
$property = new Property('a');
$property->setType('int')->setReadOnly();
$property->validate();
