<?php

declare(strict_types=1);

use Nette\PhpGenerator\ArrowFunction;


require __DIR__ . '/../bootstrap.php';


$function = new ArrowFunction;
$function
	->setReturnReference(true)
	->setBody('$a + $b');

$function->addParameter('a');
$function->addParameter('b');

same('fn &($a, $b) => $a + $b;', (string) $function);



$function = new ArrowFunction;
$function
	->setReturnType('array')
	->setBody('[]');

same('fn (): array => [];', (string) $function);
