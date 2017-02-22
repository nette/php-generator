<?php

use Nette\PhpGenerator\GlobalFunction;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = NULL) {
};

$function = GlobalFunction::from('func');
Assert::match(
'/**
 * global
 */
function func(stdClass $a, $b = NULL)
{
}', (string) $function);
