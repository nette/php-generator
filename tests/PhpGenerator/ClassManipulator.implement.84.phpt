<?php declare(strict_types=1);

/**
 * @phpVersion 8.4
 */

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
	public function concreteMethod()
	{
	}
}

abstract class TestAbstract extends ParentAbstract
{
}


test('implement adds interface properties with hooks and methods', function () {
	$class = new ClassType('TestClass');
	$manipulator = new ClassManipulator($class);
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
});


test('implement extends abstract class and adds abstract properties with hooks', function () {
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
});


testException('implement throws exception for regular class', function () {
	$class = new ClassType('TestClass');
	$manipulator = new ClassManipulator($class);
	$manipulator->implement(stdClass::class);
}, InvalidArgumentException::class, "'stdClass' is not an interface or abstract class.");
