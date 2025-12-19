<?php declare(strict_types=1);

use Nette\PhpGenerator\ClassManipulator;
use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public array $bar = [123];
}


testException('inheritProperty throws exception when class has no parent', function () {
	$class = new ClassType('Test');
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritProperty('bar');
}, Nette\InvalidStateException::class, "Class 'Test' has neither setExtends() nor setImplements() set.");


testException('inheritProperty throws exception when property not found in ancestors', function () {
	$class = new ClassType('Test');
	$class->setExtends('Unknown');
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritProperty('bar');
}, Nette\InvalidStateException::class, "Property 'bar' has not been found in any ancestor: Unknown");


test('inheritProperty creates property from parent', function () {
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
});


testException('inheritProperty throws exception when property already exists', function () {
	$class = new ClassType('Test');
	$class->setExtends(Foo::class);
	$manipulator = new ClassManipulator($class);
	$manipulator->inheritProperty('bar');
	$manipulator->inheritProperty('bar', returnIfExists: false);
}, Nette\InvalidStateException::class, "Cannot inherit property 'bar', because it already exists.");
