<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$dumper = new Dumper;

Assert::same('[1, 2, 3]', $dumper->dump([1, 2, 3], $dumper->wrapLength - 10));

same('[
	1,
	2,
	3,
]', $dumper->dump([1, 2, 3], $dumper->wrapLength - 8));


// ignore indent after new line
same('[
	[1, 2, 3],
]', $dumper->dump([[1, 2, 3]], $dumper->wrapLength - 8));


// counts with length of key
Assert::same('[8 => 1, 2, 3]', $dumper->dump([8 => 1, 2, 3], $dumper->wrapLength - 15));

same('[
	8 => 1,
	2,
	3,
]', $dumper->dump([8 => 1, 2, 3], $dumper->wrapLength - 13));
