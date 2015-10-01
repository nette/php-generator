<?php

/**
 * Test: Nette\PhpGenerator generator.
 * @phpversion 7
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


abstract class Class1
{
	function func1() {}
}


$res[] = ClassType::from(new class {
	public $a;
	private $b;
	function a() {}
	private function b() {}
});

$res[] = ClassType::from(new class extends Class1 {
	function a() {}
});

Assert::matchFile(__DIR__ . '/ClassType.from.php7.expect', implode("\n", $res));
