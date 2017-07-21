<?php
declare(strict_types=1);

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
