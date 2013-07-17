<?php

/**
 * Test: Nette\PhpGenerator generator.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @phpversion 5.4
 */

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';


/**
 * Trait1
 */
trait Trait1
{
	public function func1()
	{}
}

trait Trait2
{
	protected function func2()
	{}
}

abstract class Class1
{
	use Trait1, Trait2;
}

class Class2 extends Class1
{
}

$res[] = ClassType::from(new ReflectionClass('Trait1'));
$res[] = ClassType::from(new ReflectionClass('Trait2'));
$res[] = ClassType::from(new ReflectionClass('Class1'));
$res[] = ClassType::from(new ReflectionClass('Class2'));

Assert::matchFile(__DIR__ . '/ClassType.from.trait.expect', implode("\n", $res));
