<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/**
 * Trait1
 */
trait Trait1
{
	public function func1()
	{
	}
}

trait Trait2
{
	protected function func2()
	{
	}
}

abstract class Class1
{
	use Trait1;
	use Trait2;
}

class Class2 extends Class1
{
}

$res[] = ClassType::from('Trait1');
$res[] = ClassType::from('Trait2');
$res[] = ClassType::from('Class1');
$res[] = ClassType::from('Class2');

Assert::matchFile(__DIR__ . '/expected/ClassType.from.trait.expect', implode("\n", $res));
