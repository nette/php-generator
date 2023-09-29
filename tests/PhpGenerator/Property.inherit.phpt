<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public array $bar = [123];
}


// missing parent
$class = new Nette\PhpGenerator\ClassType('Test');
Assert::exception(
	fn() => $class->inheritProperty('bar'),
	Nette\InvalidStateException::class,
	"Class 'Test' has not setExtends() set.",
);

$class->setExtends('Unknown');
Assert::exception(
	fn() => $class->inheritProperty('bar'),
	Nette\InvalidStateException::class,
	"Property 'bar' has not been found in ancestor Unknown",
);


// implement property
$class = new Nette\PhpGenerator\ClassType('Test');
$class->setExtends(Foo::class);
$prop = $class->inheritProperty('bar');
Assert::match(<<<'XX'
	class Test extends Foo
	{
		public array $bar = [123];
	}

	XX, (string) $class);

Assert::same($prop, $class->inheritProperty('bar', returnIfExists: true));
Assert::exception(
	fn() => $class->inheritProperty('bar', returnIfExists: false),
	Nette\InvalidStateException::class,
	"Cannot inherit property 'bar', because it already exists.",
);
