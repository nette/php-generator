<?php

/**
 * Test: Nette\PhpGenerator\Helpers::format() & formatArgs()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\PhpGenerator\Helpers;


require __DIR__ . '/../bootstrap.php';


Assert::same( 'func', Helpers::format('func') );
Assert::same( 'func(1)', Helpers::format('func(?)', 1, 2) );

Assert::same( 'func', Helpers::formatArgs('func', array(1, 2)) );
Assert::same( 'func(1)', Helpers::formatArgs('func(?)', array(1, 2)) );
Assert::same( "func(array(1, 2))", Helpers::formatArgs('func(?)', array(array(1, 2))) );
Assert::same( 'func(1, 2)', Helpers::formatArgs('func(?*)', array(array(1, 2))) );

Assert::exception(function() {
	Helpers::formatArgs('func(?*)', array(1, 2));
}, 'Nette\InvalidArgumentException', 'Argument must be an array.');

Assert::exception(function() {
	Helpers::formatArgs('func(?, ?, ?)', array(1, 2));
}, 'Nette\InvalidArgumentException', 'Insufficient number of arguments.');

Assert::same( '$a = 2', Helpers::formatArgs('$? = ?', array('a', 2)) );
Assert::same( '$obj->a = 2', Helpers::formatArgs('$obj->? = ?', array('a', 2)) );
Assert::same( '$obj->{1} = 2', Helpers::formatArgs('$obj->? = ?', array(1, 2)) );
Assert::same( '$obj->{\' \'} = 2', Helpers::formatArgs('$obj->? = ?', array(' ', 2)) );

Assert::same( "Item", Helpers::formatMember('Item') );
Assert::same( "{'0Item'}", Helpers::formatMember('0Item') );

Assert::true( Helpers::isIdentifier('Item') );
Assert::false( Helpers::isIdentifier('0Item') );
