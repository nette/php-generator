<?php

/**
 * Test: Nette\PhpGenerator generator.
 * @phpversion 7.1
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

/** */
class ClassA
{
	public function func1(A $a, ?B $b, ?C $c = null, D $d = null, E $e, ?int $i = 1, ?array $arr = [])
	{
	}


	public function func2(): ?stdClass
	{
	}


	public function func3(): void
	{
	}
}


$res[] = ClassType::from(ClassA::class);

Assert::matchFile(__DIR__ . '/ClassType.from.php71.expect', implode("\n", $res));
