<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public function bar(int $a, ...$b): void
	{
	}
}


// missing parent
$class = new Nette\PhpGenerator\ClassType('Test');
Assert::exception(
	fn() => $class->inheritMethod('bar'),
	Nette\InvalidStateException::class,
	"Class 'Test' has neither setExtends() nor setImplements() set.",
);

$class->setExtends('Unknown1');
$class->addImplement('Unknown2');
Assert::exception(
	fn() => $class->inheritMethod('bar'),
	Nette\InvalidStateException::class,
	"Method 'bar' has not been found in any ancestor: Unknown1, Unknown2",
);


// implement method
$class = new Nette\PhpGenerator\ClassType('Test');
$class->setExtends(Foo::class);
$method = $class->inheritMethod('bar');
Assert::match(<<<'XX'
	public function bar(int $a, ...$b): void
	{
	}

	XX, (string) $method);

Assert::same($method, $class->inheritMethod('bar', returnIfExists: true));
Assert::exception(
	fn() => $class->inheritMethod('bar', returnIfExists: false),
	Nette\InvalidStateException::class,
	"Cannot inherit method 'bar', because it already exists.",
);
