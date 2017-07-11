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

class ClassB
{
	private const THE_PRIVATE_CONSTANT = 9;
	public const THE_PUBLIC_CONSTANT = 9;
}


$res[] = ClassType::from(ClassA::class);
$res[] = ClassType::from(ClassB::class);

Assert::matchFile(__DIR__ . '/ClassType.from.php71.expect', implode("\n", $res));
