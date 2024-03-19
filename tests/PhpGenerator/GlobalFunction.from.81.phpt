<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;

require __DIR__ . '/../bootstrap.php';


/** global */
#[ExampleAttribute]
function func(stdClass $a, $b = null)
{
	echo sprintf('hello, %s', 'world');
	return 1;
}


$function = GlobalFunction::from(func(...));
same(
	<<<'XX'
		/**
		 * global
		 */
		#[ExampleAttribute]
		function func(stdClass $a, $b = null)
		{
		}

		XX,
	(string) $function,
);
