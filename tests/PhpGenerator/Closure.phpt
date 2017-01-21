<?php

declare(strict_types=1);

use Nette\PhpGenerator\Closure;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$function = new Closure;
$function
	->setReturnReference(TRUE)
	->setBody('return $a + $b;');

$function->addParameter('a');
$function->addParameter('b');
$function->addUse('this');
$function->addUse('vars')
	->setReference(TRUE);

Assert::match(
'function &($a, $b) use ($this, &$vars) {
	return $a + $b;
}', (string) $function);


$closure = function (stdClass $a, $b = NULL) {};
$function = Closure::from($closure);
Assert::match(
'function (stdClass $a, $b = NULL) {
}', (string) $function);
