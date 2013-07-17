<?php

/**
 * Test: Nette\PhpGenerator generator.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

namespace Abc;

use Nette\PhpGenerator\ClassType,
	Assert,
	ReflectionClass;


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

	private $private = array();

	static public $static;

	/**
	 * Func3
	 * @return Class1
	 */
	private function & func3(array $a = array(), Class2 $b = NULL, \Abc\Unknown $c, \Xyz\Unknown $d, $e)
	{}

	final function func2()
	{}
}


$res[] = ClassType::from('Abc\Interface1');
$res[] = ClassType::from('Abc\Interface2');
$res[] = ClassType::from('Abc\Class1');
$res[] = ClassType::from(new ReflectionClass('Abc\Class2'));

Assert::matchFile(__DIR__ . '/ClassType.from.expect', implode("\n", $res));
