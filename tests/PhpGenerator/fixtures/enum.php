<?php

declare(strict_types=1);

namespace Abc;

/**
 * Description of enum.
 */
#[\ExampleAttribute]
enum Enum1
{
	/** Commented */
	case Clubs;
	#[ExampleAttribute]
	case Diamonds;
	case Hearts;
	case Spades;

	const FOO = 123;
	const BAR = self::Clubs;

	public function foo($x = self::Diamonds)
	{
	}
}


enum Enum2: string implements \Countable
{
	case GET = 'get';
	case POST = 'post';

	function count(): int
	{
	}
}

enum Enum3: int
{
	const FOO = 123;
	case A = self::FOO;
	case B = 20 + 5;
}
