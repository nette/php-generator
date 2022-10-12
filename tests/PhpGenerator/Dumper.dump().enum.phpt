<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump() enum
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum Suit
{
	case Clubs;
	case Diamonds;
	case Hearts;
	case Spades;
}

$dumper = new Dumper;
Assert::same('\Suit::Clubs', $dumper->dump(Suit::Clubs));
