<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$namespace = new PhpNamespace('');
Assert::same('', $namespace->getName());

$namespace = new PhpNamespace('Foo');
Assert::same('Foo', $namespace->getName());

$classA = $namespace->addClass('A');
Assert::same($namespace, $classA->getNamespace());

$interfaceB = $namespace->addInterface('B');
Assert::same($namespace, $interfaceB->getNamespace());

Assert::count(2, $namespace->getClasses());
Assert::type(Nette\PhpGenerator\ClassType::class, $namespace->getClasses()['A']);

Assert::exception(function () use ($namespace) {
	$namespace->addClass('A');
}, Nette\InvalidStateException::class, "Cannot add 'A', because it already exists.");

Assert::exception(function () use ($namespace) {
	$namespace->addFunction('f');
	$namespace->addFunction('f');
}, Nette\InvalidStateException::class, "Cannot add 'f', because it already exists.");
