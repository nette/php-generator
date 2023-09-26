<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


Assert::exception(
	fn() => (new PhpNamespace('Foo'))->add(new ClassType),
	Nette\InvalidArgumentException::class,
	'Class does not have a name.',
);


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

// namespaces are not changed
Assert::null($classA->getNamespace());
Assert::same('X', $classB->getNamespace()->getName());


// duplicity
Assert::noError(fn() => $namespace->add($classA));

Assert::exception(
	fn() => $namespace->add(new ClassType('a')),
	Nette\InvalidStateException::class,
	"Cannot add 'a', because it already exists.",
);
