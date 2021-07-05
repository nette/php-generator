<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpLiteral;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

ini_set('serialize_precision', '14');

$dumper = new Dumper;
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
Assert::same('"\t\n\t"', $dumper->dump("\t\n\t"));
Assert::same(
	"'I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n'",
	$dumper->dump("I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n") // Iñtërnâtiônàlizætiøn
);
Assert::same('"\rHello \$"', $dumper->dump("\rHello $"));
Assert::same("'He\\llo'", $dumper->dump('He\llo'));
Assert::same('\'He\ll\\\\\o \\\'wor\\\\\\\'ld\\\\\'', $dumper->dump('He\ll\\\o \'wor\\\'ld\\'));
Assert::same('[]', $dumper->dump([]));

// internal classes
Assert::same('[$s]', $dumper->dump([new PhpLiteral('$s')]));
same('[
	function () {
		return 1;
	},
]', $dumper->dump([(new Nette\PhpGenerator\Closure)->setBody('return 1;')]));

Assert::same('[1, 2, 3]', $dumper->dump([1, 2, 3]));
Assert::same("['a']", $dumper->dump(['a']));
Assert::same("[2 => 'a']", $dumper->dump([2 => 'a']));
Assert::same("[2 => 'a', 'b']", $dumper->dump([2 => 'a', 'b']));
Assert::same("[-2 => 'a', -1 => 'b']", $dumper->dump([-2 => 'a', -1 => 'b']));
Assert::same("[-2 => 'a', 0 => 'b']", $dumper->dump([-2 => 'a', 0 => 'b']));
Assert::same("[0 => 'a', -2 => 'b', 1 => 'c']", $dumper->dump(['a', -2 => 'b', 1 => 'c']));

$dumper->wrapLength = 100;
same("[
	[
		'a',
		'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
	],
]", $dumper->dump([['a', 'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong']]));

Assert::same(
	"['a' => 1, 0 => [\"\\r\" => \"\\r\", 0 => 2], 1 => 3]",
	$dumper->dump(['a' => 1, ["\r" => "\r", 2], 3])
);

Assert::same(
	"(object) [\n\t'a' => 1,\n\t'b' => 2,\n]",
	$dumper->dump((object) ['a' => 1, 'b' => 2])
);

Assert::same(
	"(object) [\n\t'a' => (object) [\n\t\t'b' => 2,\n\t],\n]",
	$dumper->dump((object) ['a' => (object) ['b' => 2]])
);


class Test
{
	public $a = 1;

	protected $b = 2;

	private $c = 3;
}

Assert::same(
	"\\Nette\\PhpGenerator\\Dumper::createObject('Test', [\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n\t\"\\x00Test\\x00c\" => 3,\n])",
	$dumper->dump(new Test)
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
	"\\Nette\\PhpGenerator\\Dumper::createObject('Test2', [\n\t\"\\x00Test2\\x00c\" => 4,\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n])",
	$dumper->dump(new Test2)
);
Assert::equal(new Test2, eval('return ' . $dumper->dump(new Test2) . ';'));


class Test3 implements Serializable
{
	private $a;


	public function serialize()
	{
		return '';
	}


	public function unserialize($s)
	{
	}
}

Assert::same('unserialize(\'C:5:"Test3":0:{}\')', $dumper->dump(new Test3));
Assert::equal(new Test3, eval('return ' . $dumper->dump(new Test3) . ';'));

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->dump(function () {});
}, Nette\InvalidArgumentException::class, 'Cannot dump closure.');



class TestDateTime extends DateTime
{
}

Assert::same(
	"new \\DateTime('2016-06-22 20:52:43.123400', new \\DateTimeZone('Europe/Prague'))",
	$dumper->dump(new DateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);
Assert::same(
	"new \\DateTimeImmutable('2016-06-22 20:52:43.123400', new \\DateTimeZone('Europe/Prague'))",
	$dumper->dump(new DateTimeImmutable('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);
same(
	"\\Nette\\PhpGenerator\\Dumper::createObject('TestDateTime', [
	'date' => '2016-06-22 20:52:43.123400',
	'timezone_type' => 3,
	'timezone' => 'Europe/Prague',
])",
	$dumper->dump(new TestDateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->dump(new class {
	});
}, Nette\InvalidArgumentException::class, 'Cannot dump anonymous class.');


Assert::exception(function () {
	$rec = [];
	$rec[] = &$rec;
	$dumper = new Dumper;
	$dumper->dump($rec);
}, Nette\InvalidArgumentException::class, 'Nesting level too deep or recursive dependency.');


Assert::exception(function () {
	$rec = new stdClass;
	$rec->x = &$rec;
	$dumper = new Dumper;
	$dumper->dump($rec);
}, Nette\InvalidArgumentException::class, 'Nesting level too deep or recursive dependency.');
