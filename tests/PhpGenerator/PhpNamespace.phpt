<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$namespace = new PhpNamespace;

Assert::same('A', $namespace->unresolveName('A'));
Assert::same('foo\A', $namespace->unresolveName('foo\A'));

$namespace->addUse('Bar\C');

Assert::same('Bar', $namespace->unresolveName('Bar'));
Assert::same('C', $namespace->unresolveName('bar\C'));
Assert::same('C\D', $namespace->unresolveName('Bar\C\D'));

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->unresolveName($type));
}


$namespace = new PhpNamespace('Foo');

Assert::same('\A', $namespace->unresolveName('\A'));
Assert::same('\A', $namespace->unresolveName('A'));
Assert::same('A', $namespace->unresolveName('foo\A'));

$namespace->addUse('Bar\C');

Assert::same('\Bar', $namespace->unresolveName('Bar'));
Assert::same('C', $namespace->unresolveName('\bar\C'));
Assert::same('C', $namespace->unresolveName('bar\C'));
Assert::same('C\D', $namespace->unresolveName('Bar\C\D'));

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->unresolveName($type));
}


$classA = $namespace->addClass('A');
Assert::same($namespace, $classA->getNamespace());

$interfaceB = $namespace->addInterface('B');
Assert::same($namespace, $interfaceB->getNamespace());

Assert::exception(function () use ($namespace) {
	$traitC = $namespace->addTrait('C');
	Assert::same($namespace, $traitC->getNamespace());
}, Nette\InvalidStateException::class, "Alias 'C' used already for 'Bar\\C', cannot use for 'Foo\\C'.");

$classA
	->addImplement('Foo\\A')
	->addImplement('Bar\\C')
	->addTrait('Bar\\D');

$method = $classA->addMethod('test');
$method->addParameter('a')->setTypeHint('Bar\C');
$method->addParameter('b')->setTypeHint('self');
$method->addParameter('c')->setTypeHint('parent');
$method->addParameter('d')->setTypeHint('array');
$method->addParameter('e')->setTypeHint('callable');

Assert::matchFile(__DIR__ . '/PhpNamespace.expect', (string) $namespace);
