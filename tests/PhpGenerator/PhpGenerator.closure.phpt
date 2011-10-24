<?php

/**
 * Test: Nette\Utils\PhpGenerator & closure.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\PhpGenerator\Method;



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

Assert::match(file_get_contents(__DIR__ . '/PhpGenerator.closure.expect'), (string) $function);
