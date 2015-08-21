<?php

/**
 * Test: Nette\PhpGenerator & function.
 */

use Nette\PhpGenerator\Method;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$function = new Method;
$function
	->setReturnReference(TRUE)
	->setBody('return $a + $b;');

$function->addParameter('a');
$function->addParameter('b');
$function->addUse('this');
$function->addUse('vars')
	->setReference(TRUE);

Assert::match(
'function & ($a, $b) use ($this, &$vars) {
	return $a + $b;
}', (string) $function);


/** closure */
$closure = function (stdClass $a, $b = NULL) {};
$function = Method::from($closure);
Assert::match(
'/**
 * closure
 */
function (stdClass $a, $b = NULL) {
}', (string) $function);


/** global */
function func(stdClass $a, $b = NULL) {
};

$function = Method::from('func');
Assert::match(
'/**
 * global
 */
function func(stdClass $a, $b = NULL)
{
}', (string) $function);
