<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = (new ClassType('Example'))
	->addMember($method = new Nette\PhpGenerator\Method('first'))
	->addMember($property = new Nette\PhpGenerator\Property('first'))
	->addMember($const = new Nette\PhpGenerator\Constant('FIRST'))
	->addMember($newMethod = $method->cloneWithName('second'))
	->addMember($newProperty = $property->cloneWithName('second'))
	->addMember($newConst = $const->cloneWithName('SECOND'));

Assert::same('first', $method->getName());
Assert::same('second', $newMethod->getName());
Assert::same('first', $property->getName());
Assert::same('second', $newProperty->getName());
Assert::same('FIRST', $const->getName());
Assert::same('SECOND', $newConst->getName());


Assert::exception(function () {
	$method = new Nette\PhpGenerator\Method('first');
	$method->cloneWithName('');
}, Nette\InvalidArgumentException::class);
