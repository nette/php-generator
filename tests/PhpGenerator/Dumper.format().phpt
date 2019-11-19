<?php

/**
 * Test: Nette\PhpGenerator\Dumper::format()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same('func', Dumper::format('func'));
Assert::same('func(1)', Dumper::format('func(?)', 1));
Assert::same('func(1 ? 2 : 3)', Dumper::format('func(1 \? 2 : 3)'));
Assert::same('func([1, 2])', Dumper::format('func(?)', [1, 2]));
Assert::same('func(1, 2)', Dumper::format('func(...?)', [1, 2]));
Assert::same('func(1, 2)', Dumper::format('func(?*)', [1, 2])); // old way
same(
'func(
	10,
	11,
	12,
	13,
	14,
	15,
	16,
	17,
	18,
	19,
	20,
	21,
	22,
	23,
	24,
	25,
	26,
	27,
	28,
	29,
	30,
	31,
	32,
	33,
	34,
	35,
	36
)',
	Dumper::format('func(?*)', range(10, 36))
);

Assert::exception(function () {
	Dumper::format('func(...?)', 1, 2);
}, Nette\InvalidArgumentException::class, 'Argument must be an array.');

Assert::exception(function () {
	Dumper::format('func(?)', 1, 2);
}, Nette\InvalidArgumentException::class, 'Insufficient number of placeholders.');

Assert::exception(function () {
	Dumper::format('func(?, ?, ?)', [1, 2]);
}, Nette\InvalidArgumentException::class, 'Insufficient number of arguments.');

Assert::same('$a = 2', Dumper::format('$? = ?', 'a', 2));
Assert::same('$obj->a = 2', Dumper::format('$obj->? = ?', 'a', 2));
Assert::same('$obj->{1} = 2', Dumper::format('$obj->? = ?', 1, 2));
Assert::same('$obj->{\' \'} = 2', Dumper::format('$obj->? = ?', ' ', 2));
