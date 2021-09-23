<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::noError(function () {
	new Nette\PhpGenerator\PhpNamespace(''); // global namespace
	new Nette\PhpGenerator\PhpNamespace('Iñtërnâti\ônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\PhpNamespace(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\PhpNamespace('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\PhpNamespace('abc abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\PhpNamespace('abc\\');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\PhpNamespace('\abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\PhpNamespace('Abc'))->addUse('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\PhpNamespace('Abc'))->addUse('Foo', 'a b');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\PhpNamespace('Abc'))->addUse('true');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\PhpNamespace('Abc'))->addUse('aaa', 'true');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\ClassType(null); // anonymous class
	new Nette\PhpGenerator\ClassType('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('abc abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('abc\abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('\abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('enum');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('bool');
}, Nette\InvalidArgumentException::class);


$class = new Nette\PhpGenerator\ClassType('Abc');
Assert::exception(function () use ($class) {
	$class->setExtends('*');
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->setExtends(['A', '*']);
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->addExtend('*');
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->setImplements(['A', '*']);
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->addImplement('*');
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->setTraits(['A', '*']);
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");

Assert::exception(function () use ($class) {
	$class->addTrait('*');
}, Nette\InvalidArgumentException::class, "Value '*' is not valid class name.");


Assert::noError(function () {
	new Nette\PhpGenerator\Property('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Property(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Property('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Property('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\Property('foo'))->setType('a b');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Parameter('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('$test');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\Parameter('foo'))->setType('a b');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Method('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Method('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Method(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Method('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	(new Nette\PhpGenerator\Method('foo'))->setReturnType('a b');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\GlobalFunction('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction('*');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Constant('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Constant(null);
}, TypeError::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Constant('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Constant('*');
}, Nette\InvalidArgumentException::class);
