<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$namespace = new PhpNamespace('');

Assert::same('', $namespace->getName());
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

Assert::same('Foo', $namespace->getName());
Assert::same('\A', $namespace->unresolveName('\A'));
Assert::same('\A', $namespace->unresolveName('A'));
Assert::same('A', $namespace->unresolveName('foo\A'));

Assert::same('A', $namespace->unresolveType('foo\A'));
Assert::same('null|A', $namespace->unresolveType('null|foo\A'));
Assert::same('?A', $namespace->unresolveType('?foo\A'));
Assert::same('A&\Countable', $namespace->unresolveType('foo\A&Countable'));
Assert::same('', $namespace->unresolveType(''));

$namespace->addUse('Bar\C');
Assert::same(['C' => 'Bar\\C'], $namespace->getUses());

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

Assert::count(2, $namespace->getClasses());
Assert::type(Nette\PhpGenerator\ClassType::class, $namespace->getClasses()['A']);

Assert::exception(function () use ($namespace) {
	$traitC = $namespace->addTrait('C');
	Assert::same($namespace, $traitC->getNamespace());
}, Nette\InvalidStateException::class, "Alias 'C' used already for 'Bar\\C', cannot use for 'Foo\\C'.");

$classA
	->addImplement('Foo\\A')
	->addImplement('Bar\\C')
	->addTrait('Bar\\D')
	->addAttribute('Foo\\A');

$method = $classA->addMethod('test');
$method->addAttribute('Foo\\A');
$method->setReturnType('static|Foo\\A');

$method->addParameter('a')->setType('Bar\C')->addAttribute('Bar\\D');
$method->addParameter('b')->setType('self');
$method->addParameter('c')->setType('parent');
$method->addParameter('d')->setType('array');
$method->addParameter('e')->setType('?callable');
$method->addParameter('f')->setType('Bar\C|string');

sameFile(__DIR__ . '/expected/PhpNamespace.expect', (string) $namespace);
