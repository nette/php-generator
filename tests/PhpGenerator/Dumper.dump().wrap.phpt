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
	"[
	'a' => [1, 2, 3],
	'aaaaaaaaa' => [
		1,
		2,
		3,
	],
]",
	$dumper->dump([
		'a' => [1, 2, 3],
		'aaaaaaaaa' => [1, 2, 3],
	]),
);

same(
	"[
	'single' => 1 + 2,
	'multi' => [
		1,
	],
]",
	$dumper->dump([
		'single' => new Literal('1 + 2'),
		'multi' => new Literal("[\n\t1,\n]\n"),
	]),
);

same(
	"(object) [
	'a' => [1, 2, 3],
	'aaaaaaaaa' => [
		1,
		2,
		3,
	],
]",
	$dumper->dump((object) [
		'a' => [1, 2, 3],
		'aaaaaaaaa' => [1, 2, 3],
	]),
);


$dumper = new Dumper;
$dumper->wrapLength = 100;
same("[
	[
		'a',
		'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
	],
]", $dumper->dump([['a', 'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong']]));
