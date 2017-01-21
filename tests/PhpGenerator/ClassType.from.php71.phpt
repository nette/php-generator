<?php

/**
 * Test: Nette\PhpGenerator generator.
 * @phpversion 7.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

/** */
class ClassA
{
	function func1(A $a, ?B $b, ?C $c = NULL, D $d = NULL, E $e, ?int $i = 1, ?array $arr = []) {}

	function func2(): ?stdClass {}

	function func3(): void {}
}


$res[] = ClassType::from(ClassA::class);

Assert::matchFile(__DIR__ . '/ClassType.from.php71.expect', implode("\n", $res));
