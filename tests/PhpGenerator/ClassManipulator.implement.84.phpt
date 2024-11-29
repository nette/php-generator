<?php

/**
 * @phpVersion 8.4
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassManipulator;
use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


interface ParentInterface
{
	public array $interfaceProperty { get; }
	public function interfaceMethod();
}

interface TestInterface extends ParentInterface
{
}

abstract class ParentAbstract
{
	abstract public array $abstractProperty { get; }
	public array $concreteProperty;
	abstract public function abstractMethod();
	public function concreteMethod() {}
}

abstract class TestAbstract extends ParentAbstract
{
}


$class = new ClassType('TestClass');
$manipulator = new ClassManipulator($class);

// Test interface implementation
$manipulator->implement(TestInterface::class);
Assert::match(<<<'XX'
	class TestClass implements TestInterface
	{
		public array $interfaceProperty;


		function interfaceMethod()
		{
		}
	}

	XX, (string) $class);


// Test abstract class extension
$class = new ClassType('TestClass');
$manipulator = new ClassManipulator($class);
$manipulator->implement(TestAbstract::class);
Assert::match(<<<'XX'
	class TestClass extends TestAbstract
	{
		public array $abstractProperty;


		public function abstractMethod()
		{
		}
	}

	XX, (string) $class);


// Test exception for regular class
Assert::exception(
	fn() => $manipulator->implement(stdClass::class),
	InvalidArgumentException::class,
	"'stdClass' is not an interface or abstract class."
);
