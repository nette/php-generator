<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

declare(strict_types=1);

namespace Abc;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Factory;
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
	private function &func3(array $a = [], Class2 $b = NULL, \Abc\Unknown $c, \Xyz\Unknown $d, callable $e, $f = Unknown::ABC, $g)
	{}

	final function func2()
	{}
}

class Class3
{
	public $prop1;
}

class Class4
{
	const THE_CONSTANT = 9;
}

$res[] = ClassType::from(Interface1::class);
$res[] = ClassType::from(Interface2::class);
$res[] = ClassType::from(Class1::class);
$res[] = ClassType::from(new Class2);
$obj = new Class3;
$obj->prop2 = 1;
$res[] = (new Factory)->fromClassReflection(new \ReflectionObject($obj));
$res[] = ClassType::from(Class4::class);

Assert::matchFile(__DIR__ . '/ClassType.from.expect', implode("\n", $res));
