<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = null)
{
	echo sprintf('hello, %s', 'world');
	return 1;
}


$function = GlobalFunction::from('func');
same(
	'/**
 * global
 */
function func(stdClass $a, $b = null)
{
}
',
	(string) $function
);


$function = GlobalFunction::withBodyFrom('func');
same(<<<'XX'
/**
 * global
 */
function func(stdClass $a, $b = null)
{
	echo \sprintf('hello, %s', 'world');
	return 1;
}

XX
, (string) $function);
