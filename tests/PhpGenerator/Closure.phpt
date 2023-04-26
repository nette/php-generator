<?php

declare(strict_types=1);

use Nette\PhpGenerator\Closure;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$function = new Closure;
$function
	->setReturnReference()
	->setBody('return $a + $b;');

$function->addParameter('a');
$function->addParameter('b');
$function->addUse('this');
$function->addUse('vars')
	->setReference();

same(
	<<<'XX'
		function &($a, $b) use ($this, &$vars) {
			return $a + $b;
		}
		XX,
	(string) $function,
);


$uses = $function->getUses();
Assert::count(2, $uses);
Assert::type(Nette\PhpGenerator\Parameter::class, $uses[0]);
Assert::type(Nette\PhpGenerator\Parameter::class, $uses[1]);

$uses = $function->setUses([$uses[0]]);

same(
	<<<'XX'
		function &($a, $b) use ($this) {
			return $a + $b;
		}
		XX,
	(string) $function,
);



Assert::exception(function () {
	$function = new Closure;
	$function->setUses([123]);
}, TypeError::class);



$function = new Closure;
$function
	->setReturnType('array')
	->setBody('return [];')
	->addUse('this');

same(
	<<<'XX'
		function () use ($this): array {
			return [];
		}
		XX,
	(string) $function,
);



$function = new Closure;
$function->setBody('return $a + $b;');
$function->addAttribute('ExampleAttribute');

same(
	<<<'XX'
		#[ExampleAttribute] function () {
			return $a + $b;
		}
		XX,
	(string) $function,
);



$function = new Closure;
$function->setBody('return $a + $b;');
$function->addAttribute('Foo', ['a', str_repeat('b', 120)]);
$function->addAttribute('Bar');

same(
	<<<'XX'
		#[Foo(
			'a',
			'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
		)]
		#[Bar]
		function () {
			return $a + $b;
		}
		XX,
	(string) $function,
);
