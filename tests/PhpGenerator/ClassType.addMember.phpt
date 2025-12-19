<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('addMember adds different types of members', function () {
	$class = (new ClassType('Example'))
		->addMember($method = new Nette\PhpGenerator\Method('getHandle'))
		->addMember($property = new Nette\PhpGenerator\Property('handle'))
		->addMember($const = new Nette\PhpGenerator\Constant('ROLE'))
		->addMember($trait = new Nette\PhpGenerator\TraitUse('Foo\Bar'));

	Assert::same(['getHandle' => $method], $class->getMethods());
	Assert::same(['handle' => $property], $class->getProperties());
	Assert::same(['ROLE' => $const], $class->getConstants());
	Assert::same(['Foo\Bar' => $trait], $class->getTraits());
	Assert::same('', $method->getBody());
});


testException('addMember throws exception on duplicate member', function () {
	$class = new ClassType('Example');
	$class->addMember(new Nette\PhpGenerator\Method('foo'));
	$class->addMember(new Nette\PhpGenerator\Method('FOO'));
}, Nette\InvalidStateException::class, "Cannot add member 'FOO', because it already exists.");


test('addMember with overwrite replaces existing member', function () {
	$class = new ClassType('Example');
	$class->addMember(new Nette\PhpGenerator\Method('foo'));
	$class->addMember($new = new Nette\PhpGenerator\Method('FOO'), overwrite: true);

	Assert::same($new, $class->getMethod('FOO'));
});
