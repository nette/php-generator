<?php

/**
 * Test: Nette\PhpGenerator & closure.
 *
 * @author     David Grudl
 */

use Nette\PhpGenerator\Method,
	Tester\Assert;


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

Assert::matchFile(__DIR__ . '/Method.closure.expect', (string) $function);
