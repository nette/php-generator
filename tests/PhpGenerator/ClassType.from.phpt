<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

namespace Abc;

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/**
 * Interface
 * @author John Doe
 */
interface Interface1
{
	function func1();
}

interface Interface2
{
}

abstract class Class1 implements Interface1
{
	/** @return Class1 */
	function func1()
	{}

	abstract protected function func2();
}

class Class2 extends Class1 implements Interface2
{
	/**
	 * Public
	 * @var int
	 */
	public $public;

	/** @var int */
	protected $protected = 10;

	private $private = [];

	static public $static;

	/**
	 * Func3
	 * @return Class1
	 */
	private function & func3(array $a = [], Class2 $b = NULL, \Abc\Unknown $c, \Xyz\Unknown $d, callable $e, $f)
	{}

	final function func2()
	{}
}

class Class3
{
	public $prop1;
}

$res[] = ClassType::from(Interface1::class);
$res[] = ClassType::from(Interface2::class);
$res[] = ClassType::from(Class1::class);
$res[] = ClassType::from(new \ReflectionClass(Class2::class));
$obj = new Class3;
$obj->prop2 = 1;
$res[] = ClassType::from(new \ReflectionObject($obj));

Assert::matchFile(__DIR__ . '/ClassType.from.expect', implode("\n", $res));
