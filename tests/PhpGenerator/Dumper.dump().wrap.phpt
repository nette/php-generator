<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;

require __DIR__ . '/../bootstrap.php';


$dumper = new Dumper;
$dumper->wrapLength = 21;
same(
	<<<'XX'
		[
			'a' => [1, 2, 3],
			'aaaaaaaaa' => [
				1,
				2,
				3,
			],
		]
		XX,
	$dumper->dump([
		'a' => [1, 2, 3],
		'aaaaaaaaa' => [1, 2, 3],
	]),
);

same(
	<<<'XX'
		[
			'single' => 1 + 2,
			'multi' => [
				1,
			],
		]
		XX,
	$dumper->dump([
		'single' => new Literal('1 + 2'),
		'multi' => new Literal("[\n\t1,\n]\n"),
	]),
);

same(
	<<<'XX'
		(object) [
			'a' => [1, 2, 3],
			'aaaaaaaaa' => [
				1,
				2,
				3,
			],
		]
		XX,
	$dumper->dump((object) [
		'a' => [1, 2, 3],
		'aaaaaaaaa' => [1, 2, 3],
	]),
);


$dumper = new Dumper;
$dumper->wrapLength = 100;
same(<<<'XX'
	[
		[
			'a',
			'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
		],
	]
	XX, $dumper->dump([['a', 'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong']]));
