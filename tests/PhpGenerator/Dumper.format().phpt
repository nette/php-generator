<?php

/**
 * Test: Nette\PhpGenerator\Dumper::format()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$dumper = new Dumper;
Assert::same('func', $dumper->format('func'));
Assert::same('func(1)', $dumper->format('func(?)', 1));
Assert::same('fn(?string $x = 1)', $dumper->format('fn(?string $x = ?)', 1));
Assert::same('fn(?string $x = 1)', $dumper->format('fn(\?string $x = ?)', 1));
Assert::same('func(1 ? 2 : 3)', $dumper->format('func(1 \? 2 : 3)'));
Assert::same('func([1, 2])', $dumper->format('func(?)', [1, 2]));
Assert::same('func(1, 2)', $dumper->format('func(...?)', [1, 2]));
Assert::same('func(1, 2)', $dumper->format('func(...?)', [1, 'a' => 2]));
Assert::same('func(1, a: 2)', $dumper->format('func(...?:)', [1, 'a' => 2])); // named args
Assert::same('func(1, 2)', $dumper->format('func(?*)', [1, 2])); // old way

$dumper->wrapLength = 100;
same(
	<<<'XX'
		func(
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
			36,
		)
		XX,
	$dumper->format('func(?*)', range(10, 36)),
);

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->format('func(...?)', 1, 2);
}, Nette\InvalidArgumentException::class, 'Argument must be an array.');

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->format('func(?)', 1, 2);
}, Nette\InvalidArgumentException::class, 'Insufficient number of placeholders.');

Assert::exception(function () {
	$dumper = new Dumper;
	$dumper->format('func(?, ?, ?)', [1, 2]);
}, Nette\InvalidArgumentException::class, 'Insufficient number of arguments.');

Assert::same('$a = 2', $dumper->format('$? = ?', 'a', 2));
Assert::same('$obj->a = 2', $dumper->format('$obj->? = ?', 'a', 2));
Assert::same('$obj->{1} = 2', $dumper->format('$obj->? = ?', 1, 2));
Assert::same('$obj->{\' \'} = 2', $dumper->format('$obj->? = ?', ' ', 2));
