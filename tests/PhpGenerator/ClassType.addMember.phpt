<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	(new ClassType('Example'))
		->addMember(new stdClass);
}, Nette\InvalidArgumentException::class, 'Argument must be Method|Property|Constant|EnumCase|TraitUse.');


$class = (new ClassType('Example'))
	->addMember($method = new Nette\PhpGenerator\Method('GETHANDLE'))
	->addMember($method = new Nette\PhpGenerator\Method('getHandle'))
	->addMember($property = new Nette\PhpGenerator\Property('handle'))
	->addMember($const = new Nette\PhpGenerator\Constant('ROLE'))
	->addMember($trait = new Nette\PhpGenerator\TraitUse('Foo\Bar'));

Assert::same(['getHandle' => $method], $class->getMethods());
Assert::same(['handle' => $property], $class->getProperties());
Assert::same(['ROLE' => $const], $class->getConstants());
Assert::same(['Foo\Bar'], $class->getTraits());
Assert::same('', $method->getBody());


$class = (new ClassType('Example'))
	->setType('interface')
	->addMember($method = new Nette\PhpGenerator\Method('getHandle'));
