<?php

use Nette\PhpGenerator\GlobalFunction;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = null)
{
}


$function = GlobalFunction::from('func');
Assert::match(
'/**
 * global
 */
function func(stdClass $a, $b = null)
{
}', (string) $function);
