<?php

/**
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


#[ExampleAttribute]


function func(stdClass $a, $b = null)
{
	return 1;
}


$function = GlobalFunction::from('func');
same(
	'#[ExampleAttribute]
function func(stdClass $a, $b = null)
{
}
',
	(string) $function,
);
