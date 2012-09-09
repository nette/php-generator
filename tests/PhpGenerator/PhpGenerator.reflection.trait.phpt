<?php

/**
 * Test: Nette\Utils\PhpGenerator generator.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 * @phpversion 5.4
 */

use Nette\Utils\PhpGenerator\ClassType;



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

Assert::match(file_get_contents(__DIR__ . '/PhpGenerator.reflection.trait.expect'), implode("\n", $res));
