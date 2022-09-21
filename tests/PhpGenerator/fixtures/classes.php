<?php

/**
 * First comment
 */

declare(strict_types=1);

/**
 * Second comment
 */

namespace Abc;

/**
 * Interface
 * @author John Doe
 */
interface Interface1
{
	public function func1();
}


interface Interface2
{
}


interface Interface3 extends Interface1
{
}

interface Interface4 extends Interface3, Interface2
{
}

abstract class Class1 implements Interface1
{
	/** @return Class1 */
	public function func1()
	{
	}


	abstract protected function func2();
}


class Class2 extends Class1 implements Interface2
{
	/**
	 * Public
	 * @var int
	 */
	public $public;

	/** @var int */
	protected $protected = 10;

	private $private = [];

	static public $static;


	/**
	 * Func3
	 * @return Class1
	 */
	private function &func3(array $a, Class2 $b, \Abc\Unknown $c, \Xyz\Unknown $d, ?callable $e, $f)
	{
	}


	private function func4(array $a = [], Class2 $b = null, $c = Unknown::ABC)
	{
	}


	final public function func2()
	{
	}
}


class Class3
{
	public $prop1;
}


class Class4
{
	const THE_CONSTANT = 9;
}

/** */
class Class5
{
	public function func1(\A $a, ?\B $b, ?\C $c = null, \D $d = null, ?int $i = 1, ?array $arr = [])
	{
	}


	public function func2(): ?\stdClass
	{
	}


	public function func3(): void
	{
	}
}


class Class6 extends Class4
{
	/** const doc */
	private const THE_PRIVATE_CONSTANT = 9;
	public const THE_PUBLIC_CONSTANT = 9;
}


class Class7
{
	public \A $a;
	public ?\B $b;
	public ?\C $c = null;
	public ?int $i = 1;
}


class Class8
{
	public function __construct(
		public $a,
		private int|string $b = 10,
		$c = null,
	) {
	}
}


/**
 * Description of class.
 */
#[\ExampleAttribute]
#[NamedArguments(foo: 'bar', bar: [1, 2, 3])]
class Class9
{
	/** Commented */
	#[ExampleAttribute]
	#[WithArguments(true)]
	const FOO = 123;

	/** @var resource */
	#[ExampleAttribute]
	public $handle;


	/** Returns file handle */
	#[ExampleAttribute]
	public function getHandle(#[WithArguments(123)] $mode)
	{
	}
}


class Class10
{
	public string|int $prop;

	function test(mixed $param): string|int
	{
	}
}

class Class11
{
    public string|int $prop;

    function test(Class2 $param = new Class2(), \Abc\Class3 $param2 = new \Abc\Class3()): string|int
    {
    }
}
