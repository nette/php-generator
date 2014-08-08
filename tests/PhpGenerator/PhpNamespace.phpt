<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$namespace = new PhpNamespace('Foo');

Assert::same('\A', $namespace->unresolveName('A'));
Assert::same('A', $namespace->unresolveName('foo\A'));

$namespace->addUse('Bar\C');

Assert::same('\Bar', $namespace->unresolveName('Bar'));
Assert::same('C', $namespace->unresolveName('bar\C'));
Assert::same('C\D', $namespace->unresolveName('Bar\C\D'));


$classA = $namespace->addClass('A');
Assert::same($namespace, $classA->getNamespace());

$interfaceB = $namespace->addInterface('B');
Assert::same($namespace, $interfaceB->getNamespace());

Assert::exception(function() use ($namespace) {
	$traitC = $namespace->addTrait('C');
	Assert::same($namespace, $traitC->getNamespace());
}, 'Nette\InvalidStateException', "Alias 'C' used already for 'Bar\C', cannot use for 'Foo\C'.");

$classA
	->addImplement('Foo\\A')
	->addImplement('Bar\\C')
	->addTrait('Bar\\D');


Assert::matchFile(__DIR__ . '/PhpNamespace.expect', (string) $namespace);
