<?php

/**
 * Test: Nette\PhpGenerator\Helpers::format() & formatArgs()
 */

use Nette\PhpGenerator\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( 'func', Helpers::format('func') );
Assert::same( 'func(1)', Helpers::format('func(?)', 1, 2) );

Assert::same( 'func', Helpers::formatArgs('func', [1, 2]) );
Assert::same( 'func(1)', Helpers::formatArgs('func(?)', [1, 2]) );
Assert::same( "func([1, 2])", Helpers::formatArgs('func(?)', [[1, 2]]) );
Assert::same( 'func(1, 2)', Helpers::formatArgs('func(?*)', [[1, 2]]) );
Assert::same(
	"func(10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34,\n\t35, 36, 37, 38, 39, 40)",
	Helpers::formatArgs('func(?*)', [range(10, 40)])
);

Assert::exception(function() {
	Helpers::formatArgs('func(?*)', [1, 2]);
}, 'Nette\InvalidArgumentException', 'Argument must be an array.');

Assert::exception(function() {
	Helpers::formatArgs('func(?, ?, ?)', [1, 2]);
}, 'Nette\InvalidArgumentException', 'Insufficient number of arguments.');

Assert::same( '$a = 2', Helpers::formatArgs('$? = ?', ['a', 2]) );
Assert::same( '$obj->a = 2', Helpers::formatArgs('$obj->? = ?', ['a', 2]) );
Assert::same( '$obj->{1} = 2', Helpers::formatArgs('$obj->? = ?', [1, 2]) );
Assert::same( '$obj->{\' \'} = 2', Helpers::formatArgs('$obj->? = ?', [' ', 2]) );

Assert::same( "Item", Helpers::formatMember('Item') );
Assert::same( "{'0Item'}", Helpers::formatMember('0Item') );

Assert::true( Helpers::isIdentifier('Item') );
Assert::false( Helpers::isIdentifier('0Item') );
