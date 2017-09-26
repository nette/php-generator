<?php
declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class A
{
	public $a;

	protected $b;

	private $c;


	public function foo()
	{
	}
}


class B extends A
{
	public $d;

	protected $e;

	private $f;


	public function bar()
	{
		return 3;
	}
}


Assert::matchFile(__DIR__ . '/ClassType.inheritance.expect', (string) ClassType::from('B'));
