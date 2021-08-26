<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump() enum
 * @phpVersion 8.1
 */

declare(strict_types=1);
namespace ns1;

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum Suit {
	case Clubs;
	case Diamonds;
	case Hearts;
	case Spades;
}

$dumper = new Dumper;
Assert::same('\\ns1\\Suit::Clubs', $dumper->dump(Suit::Clubs));

#[\Attribute]
class MyAttr
{
	public function __construct(public Suit $suit)
	{
	}
}

$ns = new PhpNamespace("ns2");
$ns->addClass("cls")->addAttribute(MyAttr::class, [Suit::Clubs]);
eval($ns);
Assert::same((new \ReflectionClass("ns2\\cls"))->getAttributes()[0]->newInstance()::class, MyAttr::class);
