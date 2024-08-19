<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassManipulator;
use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public function bar(int $a, ...$b): void
	{
	}
}


// missing parent
$class = new ClassType('Test');
$manipulator = new ClassManipulator($class);
Assert::exception(
	fn() => $manipulator->inheritMethod('bar'),
	Nette\InvalidStateException::class,
	"Class 'Test' has neither setExtends() nor setImplements() set.",
);

$class->setExtends('Unknown1');
$class->addImplement('Unknown2');
Assert::exception(
	fn() => $manipulator->inheritMethod('bar'),
	Nette\InvalidStateException::class,
	"Method 'bar' has not been found in any ancestor: Unknown1, Unknown2",
);


// implement method
$class = new ClassType('Test');
$class->setExtends(Foo::class);
$manipulator = new ClassManipulator($class);
$method = $manipulator->inheritMethod('bar');
Assert::match(<<<'XX'
	public function bar(int $a, ...$b): void
	{
	}

	XX, (string) $method);

Assert::same($method, $manipulator->inheritMethod('bar', returnIfExists: true));
Assert::exception(
	fn() => $manipulator->inheritMethod('bar', returnIfExists: false),
	Nette\InvalidStateException::class,
	"Cannot inherit method 'bar', because it already exists.",
);
