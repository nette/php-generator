<?php

/**
 * Test: Nette\PhpGenerator\Dumper::format()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$dumper = new Dumper;
$dumper->wrapLength = 100;

Assert::same('func([1, 2, 3])', $dumper->format('func(?)', [1, 2, 3]));

same(
	<<<'XX'
		loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong([
			1,
			2,
			3,
		])
		XX,
	$dumper->format('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(?)', [1, 2, 3]),
);


same(
	<<<'XX'
		looooooooooooooooooooooooooooooooooooooooo([1, 2, 3]) + ooooooooooooooooooooooooooooooooooooooooooooooong([
			1,
			2,
			3,
		])
		XX,
	$dumper->format('looooooooooooooooooooooooooooooooooooooooo(?) + ooooooooooooooooooooooooooooooooooooooooooooooong(?)', [1, 2, 3], [1, 2, 3]),
);


// variadics
Assert::same('func(1, 2, 3)', $dumper->format('func(...?)', [1, 2, 3]));


same(
	<<<'XX'
		loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(
			1,
			2,
			3,
		)
		XX,
	$dumper->format('loooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong(...?)', [1, 2, 3]),
);


same(
	<<<'XX'
		looooooooooooooooooooooooooooooooooooooooo(1, 2, 3) + ooooooooooooooooooooooooooooooooooooooooooooooong(
			1,
			2,
			3,
		)
		XX,
	$dumper->format('looooooooooooooooooooooooooooooooooooooooo(...?) + ooooooooooooooooooooooooooooooooooooooooooooooong(...?)', [1, 2, 3], [1, 2, 3]),
);
