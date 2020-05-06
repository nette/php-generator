<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = null)
{
	echo 'hello';
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
', (string) $function);


$function = GlobalFunction::withBodyFrom('func');
same(
'/**
 * global
 */
function func(stdClass $a, $b = null)
{
	echo \'hello\';
	return 1;
}
', (string) $function);
