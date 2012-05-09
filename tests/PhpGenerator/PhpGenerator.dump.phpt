<?php

/**
 * Test: Nette\Utils\PhpGenerator\Helpers::dump()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\PhpGenerator\Helpers,
	Nette\Utils\PhpGenerator\PhpLiteral;



require __DIR__ . '/../bootstrap.php';


class Test
{
	public $a = 1;
	protected $b = 2;
	private $c = 3;
}


Assert::same( '1', Helpers::dump(1) );
Assert::same( '1.0', Helpers::dump(1.0) );
Assert::same( 'NULL', Helpers::dump(NULL) );
Assert::same( 'TRUE', Helpers::dump(TRUE) );
Assert::same( 'FALSE', Helpers::dump(FALSE) );

Assert::same( "''", Helpers::dump('') );
Assert::same( "'Hello'", Helpers::dump('Hello') );
Assert::same( "'I\xc3\xb1t\xc3\xabrn\xc3\xa2ti\xc3\xb4n\xc3\xa0liz\xc3\xa6ti\xc3\xb8n'", Helpers::dump("I\xc3\xb1t\xc3\xabrn\xc3\xa2ti\xc3\xb4n\xc3\xa0liz\xc3\xa6ti\xc3\xb8n") ); // Iñtërnâtiônàlizætiøn
Assert::same( '"\rHello \$"', Helpers::dump("\rHello $") );
Assert::same( 'array()', Helpers::dump(array()) );

Assert::same( "array(\n\t\$s,\n)", Helpers::dump(array(new PhpLiteral('$s'))) );

Assert::same( "array(\n\t1,\n\t2,\n\t3,\n)", Helpers::dump(array(1,2,3)) );
Assert::same( "array(\n\t'a',\n\t7 => 'b',\n\t'c',\n\t'9a' => 'd',\n\t'e',\n)", Helpers::dump(array('a', 7 => 'b', 'c', '9a' => 'd', 9 => 'e')) );
Assert::same( "array(\n\t'a' => 1,\n\tarray(\n\t\t\"\\r\" => \"\\r\",\n\t\t2,\n\t),\n\t3,\n)", Helpers::dump(array('a' => 1, array("\r" => "\r", 2), 3)) );

Assert::same( "(object) array(\n\t'a' => 1,\n\t'b' => 2,\n)", Helpers::dump((object) array('a' => 1, 'b' => 2)) );
Assert::same( "(object) array(\n\t'a' => (object) array(\n\t\t'b' => 2,\n\t),\n)" , Helpers::dump((object) array('a' => (object) array('b' => 2))) );
Assert::same( "Nette\\Utils\\PhpGenerator\\Helpers::createObject('Test', array(\n\t'a' => 1,\n\t'b' => 2,\n\t'c' => 3,\n))", Helpers::dump(new Test) );
