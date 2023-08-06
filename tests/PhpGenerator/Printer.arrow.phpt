<?php

declare(strict_types=1);

use Nette\PhpGenerator\Closure;
use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$function = new Closure;
$function
	->setReturnReference(true)
	->setBody('$a + $b');

$function->addParameter('a');
$function->addParameter('b');

Assert::same('fn&($a, $b) => $a + $b;', (new Printer)->printArrowFunction($function));



$function = new Closure;
$function
	->setReturnType('array')
	->setBody('[]');

Assert::same('fn(): array => [];', (new Printer)->printArrowFunction($function));


Assert::exception(function () {
	$function = new Closure;
	$function->addUse('vars')
		->setReference(true);
	(new Printer)->printArrowFunction($function);
}, Nette\InvalidArgumentException::class, 'Arrow function cannot bind variables by-reference.');
