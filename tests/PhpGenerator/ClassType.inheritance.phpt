<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;

require __DIR__ . '/../bootstrap.php';


interface I1
{
}


interface I2
{
}


interface I3 extends I2
{
}


class A implements I1
{
	public $a;

	protected $b;

	private $c;


	public function foo()
	{
	}
}


class B extends A implements I3
{
	public $d;

	protected $e;

	private $f;


	public function bar()
	{
		return 3;
	}
}


sameFile(__DIR__ . '/expected/ClassType.inheritance.expect', (string) ClassType::from(B::class));
