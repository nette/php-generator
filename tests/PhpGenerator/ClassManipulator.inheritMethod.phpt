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


testException('inheritMethod throws exception when class has no parent', function () {
	$class = new ClassType('Test');
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritMethod('bar');
}, Nette\InvalidStateException::class, "Class 'Test' has neither setExtends() nor setImplements() set.");


testException('inheritMethod throws exception when method not found in ancestors', function () {
	$class = new ClassType('Test');
	$class->setExtends('Unknown1');
	$class->addImplement('Unknown2');
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritMethod('bar');
}, Nette\InvalidStateException::class, "Method 'bar' has not been found in any ancestor: Unknown1, Unknown2");


test('inheritMethod creates method from parent', function () {
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
});


testException('inheritMethod throws exception when method already exists', function () {
	$class = new ClassType('Test');
	$class->setExtends(Foo::class);
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritMethod('bar');
	$manipulator->inheritMethod('bar', returnIfExists: false);
}, Nette\InvalidStateException::class, "Cannot inherit method 'bar', because it already exists.");
