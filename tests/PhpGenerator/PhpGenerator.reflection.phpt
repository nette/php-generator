<?php

/**
 * Test: Nette\Utils\PhpGenerator generator.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 * @phpversion 5.3
 */

namespace Abc;

use Nette\Utils\PhpGenerator\ClassType,
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
	private function & func3(array $a = array(), Class2 $b = NULL, \Abc\Unknown $c, \Xyz\Unknown $d)
	{}

	final function func2()
	{}
}


$res[] = ClassType::from('Abc\Interface1');
$res[] = ClassType::from('Abc\Interface2');
$res[] = ClassType::from('Abc\Class1');
$res[] = ClassType::from(new ReflectionClass('Abc\Class2'));

Assert::match(file_get_contents(__DIR__ . '/PhpGenerator.reflection.expect'), implode("\n", $res));
