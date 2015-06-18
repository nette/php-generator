<?php

use Nette\PhpGenerator\ClassType;
use ReflectionClass;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class A
{
	public $a;
	protected $b;
	private $c;

	function foo()
	{
	}
}


class B extends A
{
	public $d;
	protected $e;
	private $f;

	function bar()
	{
		return 3;
	}
}


Assert::matchFile(__DIR__ . '/ClassType.inheritance.expect', (string) ClassType::from('B'));
