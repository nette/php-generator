<?php

/**
 * Test: Nette\PhpGenerator\Dumper::format()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Dumper::setWrapLength(100);
$dumper = new Dumper;

Assert::same('func([1, 2, 3])', $dumper->format('func(?)', [1, 2, 3]));

same('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong([
	1,
	2,
	3,
])', $dumper->format('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(?)', [1, 2, 3]));


same('looooooooooooooooooooooooooooooooooooooooo([1, 2, 3]) + ooooooooooooooooooooooooooooooooooooooooooooooong([
	1,
	2,
	3,
])', $dumper->format('looooooooooooooooooooooooooooooooooooooooo(?) + ooooooooooooooooooooooooooooooooooooooooooooooong(?)', [1, 2, 3], [1, 2, 3]));


// variadics
Assert::same('func(1, 2, 3)', $dumper->format('func(...?)', [1, 2, 3]));


same('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(
	1,
	2,
	3
)', $dumper->format('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(...?)', [1, 2, 3]));


same('looooooooooooooooooooooooooooooooooooooooo(1, 2, 3) + ooooooooooooooooooooooooooooooooooooooooooooooong(
	1,
	2,
	3
)', $dumper->format('looooooooooooooooooooooooooooooooooooooooo(...?) + ooooooooooooooooooooooooooooooooooooooooooooooong(...?)', [1, 2, 3], [1, 2, 3]));
