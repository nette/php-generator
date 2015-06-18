<?php

/**
 * Test: Nette\PhpGenerator\Helpers::dump()
 */

use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpLiteral;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same('1', Helpers::dump(1));
Assert::same('1.0', Helpers::dump(1.0));
Assert::same('NULL', Helpers::dump(NULL));
Assert::same('TRUE', Helpers::dump(TRUE));
Assert::same('FALSE', Helpers::dump(FALSE));

Assert::same("''", Helpers::dump(''));
Assert::same("'Hello'", Helpers::dump('Hello'));
Assert::same("'I\xc3\xb1t\xc3\xabrn\xc3\xa2ti\xc3\xb4n\xc3\xa0liz\xc3\xa6ti\xc3\xb8n'", Helpers::dump("I\xc3\xb1t\xc3\xabrn\xc3\xa2ti\xc3\xb4n\xc3\xa0liz\xc3\xa6ti\xc3\xb8n")); // Iñtërnâtiônàlizætiøn
Assert::same('"\rHello \$"', Helpers::dump("\rHello $"));
Assert::same("'He\\llo'", Helpers::dump('He\llo'));
Assert::same('\'He\ll\\\\\o \\\'wor\\\\\\\'ld\\\\\'', Helpers::dump('He\ll\\\o \'wor\\\'ld\\'));
Assert::same('[]', Helpers::dump([]));

Assert::same("[\$s]", Helpers::dump([new PhpLiteral('$s')]));

Assert::same('[1, 2, 3]', Helpers::dump([1, 2, 3]));
Assert::same("['a', 7 => 'b', 'c', '9a' => 'd', 'e']", Helpers::dump(['a', 7 => 'b', 'c', '9a' => 'd', 9 => 'e']));
Assert::same("[\n\t[\n\t\t'a',\n\t\t'loooooooooooooooooooooooooooooooooong',\n\t],\n]", Helpers::dump([['a', 'loooooooooooooooooooooooooooooooooong']]));
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
	private $c = 4;
	public $d = 5;

	function __sleep()
	{
		return ['c', 'b', 'a'];
	}

	function __wakeup()
	{
	}
}

Assert::same("Nette\\PhpGenerator\\Helpers::createObject('Test2', [\n\t\"\\x00Test2\\x00c\" => 4,\n\t'a' => 1,\n\t\"\\x00*\\x00b\" => 2,\n])", Helpers::dump(new Test2));
Assert::equal(new Test2, eval('return ' . Helpers::dump(new Test2) . ';'));


class Test3 implements Serializable
{
	private $a;

	function serialize()
	{
		return '';
	}

	function unserialize($s)
	{
	}
}

Assert::same('unserialize(\'C:5:"Test3":0:{}\')', Helpers::dump(new Test3));
Assert::equal(new Test3, eval('return ' . Helpers::dump(new Test3) . ';'));
