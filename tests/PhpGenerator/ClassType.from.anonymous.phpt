<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;

require __DIR__ . '/../bootstrap.php';


abstract class Class1
{
	public function func1()
	{
	}
}


$res[] = ClassType::from(new class {
	public $a;

	private $b;


	public function a()
	{
	}


	private function b()
	{
	}
});

$res[] = ClassType::from(new class extends Class1 {
	public function a()
	{
	}
});

sameFile(__DIR__ . '/expected/ClassType.from.anonymous.expect', implode("\n", $res));
