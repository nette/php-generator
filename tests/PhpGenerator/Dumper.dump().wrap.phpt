<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;


require __DIR__ . '/../bootstrap.php';


$dumper = new Dumper;
$dumper->wrapLength = 21;
same("[
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
	])
);

same("(object) [
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
	])
);


$dumper = new Dumper;
$dumper->wrapLength = 100;
same("[
	[
		'a',
		'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
	],
]", $dumper->dump([['a', 'looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong']]));
