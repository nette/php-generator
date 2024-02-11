<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

ini_set('serialize_precision', '14');

$dumper = new Dumper;

// scalars
Assert::same('0', $dumper->dump(0));
Assert::same('1', $dumper->dump(1));
Assert::same('0.0', $dumper->dump(0.0));
Assert::same('1.0', $dumper->dump(1.0));
Assert::same('0.1', $dumper->dump(0.1));
Assert::same('INF', $dumper->dump(INF));
Assert::same('-INF', $dumper->dump(-INF));
Assert::same('NAN', $dumper->dump(NAN));
Assert::same('null', $dumper->dump(null));
Assert::same('true', $dumper->dump(true));
Assert::same('false', $dumper->dump(false));

Assert::same("''", $dumper->dump(''));
Assert::same("'Hello'", $dumper->dump('Hello'));
Assert::same('"\t\n\r\e"', $dumper->dump("\t\n\r\e"));
Assert::same('"\u{FEFF}"', $dumper->dump("\xEF\xBB\xBF")); // BOM
Assert::same('\'$"\\\\\'', $dumper->dump('$"\\'));
Assert::same('\'$"\\ \x00\'', $dumper->dump('$"\\ \x00')); // no escape
Assert::same('"\\$\\"\\\\ \x00"', $dumper->dump("$\"\\ \x00"));
Assert::same(
	"'I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n'",
	$dumper->dump("I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n"), // Iñtërnâtiônàlizætiøn
);
Assert::same('"\rHello \$"', $dumper->dump("\rHello $"));
Assert::same("'He\\llo'", $dumper->dump('He\llo'));
Assert::same('\'He\ll\\\\\o \\\'wor\\\\\\\'ld\\\\\'', $dumper->dump('He\ll\\\o \'wor\\\'ld\\'));


// literal
Assert::same('[$s]', $dumper->dump([new Literal('$s')]));
Assert::same("[strlen('hello')]", $dumper->dump([new Literal('strlen(?)', ['hello'])]));
Assert::same("a\nb", $dumper->dump(new Literal("a\r\nb")));


// Literal::new
Assert::same('new stdClass()', $dumper->dump(Literal::new('stdClass')));
Assert::same('new stdClass(10, 20)', $dumper->dump(Literal::new('stdClass', [10, 20])));
Assert::same('new stdClass(10, c: 20)', $dumper->dump(Literal::new('stdClass', [10, 'c' => 20])));


// arrays
Assert::same('[]', $dumper->dump([]));
Assert::same('[1, 2, 3]', $dumper->dump([1, 2, 3]));
Assert::same("['a']", $dumper->dump(['a']));
Assert::same("[2 => 'a']", $dumper->dump([2 => 'a']));
Assert::same("[2 => 'a', 'b']", $dumper->dump([2 => 'a', 'b']));
Assert::same("[-2 => 'a', 'b']", $dumper->dump([-2 => 'a', -1 => 'b']));
Assert::same("[-2 => 'a', 0 => 'b']", $dumper->dump([-2 => 'a', 0 => 'b']));
Assert::same("[0 => 'a', -2 => 'b', 1 => 'c']", $dumper->dump(['a', -2 => 'b', 1 => 'c']));


// stdClass
Assert::same(
	"(object) ['a' => 1, 'b' => 2]",
	$dumper->dump((object) ['a' => 1, 'b' => 2]),
);

Assert::same(
	"(object) ['a' => (object) ['b' => 2]]",
	$dumper->dump((object) ['a' => (object) ['b' => 2]]),
);


// objects
class Test
{
	public $a = 1;

	protected $b = 2;

	private $c = 3;
}

Assert::same(
	"\\Nette\\PhpGenerator\\Dumper::createObject(\\Test::class, [\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n\t\"\\x00Test\\x00c\" => 3,\n])",
	$dumper->dump(new Test),
);
Assert::equal(new Test, eval('return ' . $dumper->dump(new Test) . ';'));


class Test2 extends Test
{
	public $d = 5;

	private $c = 4;


	public function __sleep()
	{
		return ['c', 'b', 'a'];
	}


	public function __wakeup()
	{
	}
}

Assert::same(
	PHP_VERSION_ID < 80100
		? "\\Nette\\PhpGenerator\\Dumper::createObject(\\Test2::class, [\n\t\"\\x00Test2\\x00c\" => 4,\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n])"
		: "\\Nette\\PhpGenerator\\Dumper::createObject(\\Test2::class, [\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n\t\"\\x00Test2\\x00c\" => 4,\n])",
	$dumper->dump(new Test2),
);
Assert::equal(new Test2, eval('return ' . $dumper->dump(new Test2) . ';'));


Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->dump(new class {
	});
}, Nette\InvalidStateException::class, 'Cannot dump an instance of an anonymous class.');



// closures
Assert::same(
	PHP_VERSION_ID < 80100
		? "\\Closure::fromCallable('strlen')"
		: 'strlen(...)',
	$dumper->dump(Closure::fromCallable('strlen')),
);

Assert::same(
	PHP_VERSION_ID < 80100
		? "\\Closure::fromCallable(['Nette\\PhpGenerator\\ClassLike', 'from'])"
		: 'Nette\PhpGenerator\ClassLike::from(...)',
	$dumper->dump(Closure::fromCallable([Nette\PhpGenerator\ClassLike::class, 'from'])),
);

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->dump(function () {});
}, Nette\InvalidStateException::class, 'Cannot dump object of type Closure.');



// __serialize
class TestSer
{
	public function __serialize(): array
	{
		return ['a', 'b'];
	}


	public function __unserialize(array $data): void
	{
	}
}


$dumper = new Dumper;
Assert::same("\\Nette\\PhpGenerator\\Dumper::createObject(\\TestSer::class, [\n\t0 => 'a',\n\t1 => 'b',\n])", $dumper->dump(new TestSer));
Assert::equal(new TestSer, eval('return ' . $dumper->dump(new TestSer) . ';'));



// datetime
class TestDateTime extends DateTime
{
}

Assert::same(
	"new \\DateTime('2016-06-22 20:52:43.123400', new \\DateTimeZone('Europe/Prague'))",
	$dumper->dump(new DateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague'))),
);
Assert::same(
	"new \\DateTimeImmutable('2016-06-22 20:52:43.123400', new \\DateTimeZone('Europe/Prague'))",
	$dumper->dump(new DateTimeImmutable('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague'))),
);
same(
	<<<'XX'
		\Nette\PhpGenerator\Dumper::createObject(\TestDateTime::class, [
			'date' => '2016-06-22 20:52:43.123400',
			'timezone_type' => 3,
			'timezone' => 'Europe/Prague',
		])
		XX,
	$dumper->dump(new TestDateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague'))),
);


// disallow custom objects
$dumper = new Dumper;
$dumper->customObjects = false;
Assert::exception(
	fn() => $dumper->dump(new TestSer),
	Nette\InvalidStateException::class,
	'Cannot dump object of type TestSer.',
);
