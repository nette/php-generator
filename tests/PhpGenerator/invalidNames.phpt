<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::noError(function () {
	new Nette\PhpGenerator\PhpNamespace(''); // global namespace
	new Nette\PhpGenerator\PhpNamespace(NULL); // global namespace for back compatibility
	new Nette\PhpGenerator\PhpNamespace('Iñtërnâti\ônàlizætiøn');
});

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
	new Nette\PhpGenerator\PhpNamespace('\\abc');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\ClassType(NULL); // anonymous class
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
	new Nette\PhpGenerator\ClassType('abc\\abc');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\ClassType('\\abc');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Property('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Property(NULL);
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Property('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Property('*');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Parameter('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter(NULL);
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('*');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Parameter('$test');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Method('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Method('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Method(NULL);
}, Nette\DeprecatedException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Method('*');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\GlobalFunction('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction(NULL);
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\GlobalFunction('*');
}, Nette\InvalidArgumentException::class);


Assert::noError(function () {
	new Nette\PhpGenerator\Constant('Iñtërnâtiônàlizætiøn');
});

Assert::exception(function () {
	new Nette\PhpGenerator\Constant(NULL);
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Constant('');
}, Nette\InvalidArgumentException::class);

Assert::exception(function () {
	new Nette\PhpGenerator\Constant('*');
}, Nette\InvalidArgumentException::class);
