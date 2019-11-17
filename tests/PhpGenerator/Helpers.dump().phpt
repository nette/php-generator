<?php

/**
 * Test: Nette\PhpGenerator\Helpers::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpLiteral;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

ini_set('serialize_precision', '14');

Assert::same('0', Helpers::dump(0));
Assert::same('1', Helpers::dump(1));
Assert::same('0.0', Helpers::dump(0.0));
Assert::same('1.0', Helpers::dump(1.0));
Assert::same('0.1', Helpers::dump(0.1));
Assert::same('INF', Helpers::dump(INF));
Assert::same('-INF', Helpers::dump(-INF));
Assert::same('NAN', Helpers::dump(NAN));
Assert::same('null', Helpers::dump(null));
Assert::same('true', Helpers::dump(true));
Assert::same('false', Helpers::dump(false));

Assert::same("''", Helpers::dump(''));
Assert::same("'Hello'", Helpers::dump('Hello'));
Assert::same('"\t\n\t"', Helpers::dump("\t\n\t"));
Assert::same("'I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n'", Helpers::dump("I\u{F1}t\u{EB}rn\u{E2}ti\u{F4}n\u{E0}liz\u{E6}ti\u{F8}n")); // Iñtërnâtiônàlizætiøn
Assert::same('"\rHello \$"', Helpers::dump("\rHello $"));
Assert::same("'He\\llo'", Helpers::dump('He\llo'));
Assert::same('\'He\ll\\\\\o \\\'wor\\\\\\\'ld\\\\\'', Helpers::dump('He\ll\\\o \'wor\\\'ld\\'));
Assert::same('[]', Helpers::dump([]));

Assert::same('[$s]', Helpers::dump([new PhpLiteral('$s')]));

Assert::same('[1, 2, 3]', Helpers::dump([1, 2, 3]));
Assert::same('[1, 2, 3]', Helpers::dump([1, 2, 3], 91));
same('[
	1,
	2,
	3,
]', Helpers::dump([1, 2, 3], 92));
Assert::same("['a', 7 => 'b', 'c', '9a' => 'd', 'e']", Helpers::dump(['a', 7 => 'b', 'c', '9a' => 'd', 9 => 'e']));
same("[
	[
		'a',
		'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
	],
]", Helpers::dump([['a', 'loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong']]));
Assert::same("['a' => 1, [\"\\r\" => \"\\r\", 2], 3]", Helpers::dump(['a' => 1, ["\r" => "\r", 2], 3]));

Assert::same("(object) [\n\t'a' => 1,\n\t'b' => 2,\n]", Helpers::dump((object) ['a' => 1, 'b' => 2]));
Assert::same("(object) [\n\t'a' => (object) [\n\t\t'b' => 2,\n\t],\n]", Helpers::dump((object) ['a' => (object) ['b' => 2]]));

class Test
{
	public $a = 1;

	protected $b = 2;

	private $c = 3;
}

Assert::same("Nette\\PhpGenerator\\Helpers::createObject('Test', [\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n\t\"\\x00Test\\x00c\" => 3,\n])", Helpers::dump(new Test));
Assert::equal(new Test, eval('return ' . Helpers::dump(new Test) . ';'));


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

Assert::same("Nette\\PhpGenerator\\Helpers::createObject('Test2', [\n\t\"\\x00Test2\\x00c\" => 4,\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n])", Helpers::dump(new Test2));
Assert::equal(new Test2, eval('return ' . Helpers::dump(new Test2) . ';'));


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

Assert::same('unserialize(\'C:5:"Test3":0:{}\')', Helpers::dump(new Test3));
Assert::equal(new Test3, eval('return ' . Helpers::dump(new Test3) . ';'));

Assert::exception(function () {
	Helpers::dump(function () {});
}, Nette\InvalidArgumentException::class, 'Cannot dump closure.');



class TestDateTime extends DateTime
{
}

Assert::same(
	"new DateTime('2016-06-22 20:52:43.123400', new DateTimeZone('Europe/Prague'))",
	Helpers::dump(new DateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);
Assert::same(
	"new DateTimeImmutable('2016-06-22 20:52:43.123400', new DateTimeZone('Europe/Prague'))",
	Helpers::dump(new DateTimeImmutable('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);
same(
	"Nette\\PhpGenerator\\Helpers::createObject('TestDateTime', [
	'date' => '2016-06-22 20:52:43.123400',
	'timezone_type' => 3,
	'timezone' => 'Europe/Prague',
])",
	Helpers::dump(new TestDateTime('2016-06-22 20:52:43.1234', new DateTimeZone('Europe/Prague')))
);

Assert::exception(function () {
	Helpers::dump(new class {
	});
}, Nette\InvalidArgumentException::class, 'Cannot dump anonymous class.');
