<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassManipulator;
use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public array $bar = [123];
}


// missing parent
$class = new ClassType('Test');
$manipulator = new ClassManipulator($class);
Assert::exception(
	fn() => $manipulator->inheritProperty('bar'),
	Nette\InvalidStateException::class,
	"Class 'Test' has not setExtends() set.",
);

$class->setExtends('Unknown');
Assert::exception(
	fn() => $manipulator->inheritProperty('bar'),
	Nette\InvalidStateException::class,
	"Property 'bar' has not been found in ancestor Unknown",
);


// implement property
$class = new ClassType('Test');
$class->setExtends(Foo::class);
$manipulator = new ClassManipulator($class);
$prop = $manipulator->inheritProperty('bar');
Assert::match(<<<'XX'
	class Test extends Foo
	{
		public array $bar = [123];
	}

	XX, (string) $class);

Assert::same($prop, $manipulator->inheritProperty('bar', returnIfExists: true));
Assert::exception(
	fn() => $manipulator->inheritProperty('bar', returnIfExists: false),
	Nette\InvalidStateException::class,
	"Cannot inherit property 'bar', because it already exists.",
);
