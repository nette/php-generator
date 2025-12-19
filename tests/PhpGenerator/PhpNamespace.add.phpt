<?php declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


testException('adding class without name throws exception', function () {
	(new PhpNamespace('Foo'))->add(new ClassType);
}, Nette\InvalidArgumentException::class, 'Class does not have a name.');


test('adding classes preserves their original namespaces', function () {
	$namespace = (new PhpNamespace('Foo'))
		->add($classA = new ClassType('A'))
		->add($classB = new ClassType('B', new PhpNamespace('X')));

	same(
		<<<'XX'
			namespace Foo;

			class A
			{
			}

			class B
			{
			}

			XX,
		(string) $namespace,
	);

	Assert::null($classA->getNamespace());
	Assert::same('X', $classB->getNamespace()->getName());
});


test('adding same class again does not throw error', function () {
	$namespace = new PhpNamespace('Foo');
	$classA = new ClassType('A');
	$namespace->add($classA);

	Assert::noError(fn() => $namespace->add($classA));
});


testException('adding class with duplicate name throws exception', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->add(new ClassType('a'));
	$namespace->add(new ClassType('a'));
}, Nette\InvalidStateException::class, "Cannot add 'a', because it already exists.");
