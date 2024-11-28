<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassManipulator;
use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


interface TestInterface
{
	public function testMethod();
}

$class = new ClassType('TestClass');
$manipulator = new ClassManipulator($class);

// Test valid interface implementation
$manipulator->implement(TestInterface::class);
Assert::true(in_array(TestInterface::class, $class->getImplements(), true));
Assert::true($class->hasMethod('testMethod'));

// Test exception for non-interface
Assert::exception(
	fn() => $manipulator->implement(stdClass::class),
	InvalidArgumentException::class,
);
