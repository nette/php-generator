<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// global namespace
$namespace = new PhpNamespace('');

Assert::same('', $namespace->getName());
Assert::same('A', $namespace->simplifyName('A'));
Assert::same('foo\A', $namespace->simplifyName('foo\A'));

$namespace->addUse('Bar\C');

Assert::same('Bar', $namespace->simplifyName('Bar'));
Assert::same('C', $namespace->simplifyName('bar\C'));
Assert::same('C\D', $namespace->simplifyName('Bar\C\D'));

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->simplifyName($type));
}


// namespace
$namespace = new PhpNamespace('Foo');

Assert::same('Foo', $namespace->getName());
Assert::same('\A', $namespace->simplifyName('\A'));
Assert::same('\A', $namespace->simplifyName('A'));
Assert::same('A', $namespace->simplifyName('foo\A'));

Assert::same('A', $namespace->simplifyType('foo\A'));
Assert::same('null|A', $namespace->simplifyType('null|foo\A'));
Assert::same('?A', $namespace->simplifyType('?foo\A'));
Assert::same('A&\Countable', $namespace->simplifyType('foo\A&Countable'));
Assert::same('', $namespace->simplifyType(''));

$namespace->addUse('Foo');
Assert::same('B', $namespace->simplifyName('Foo\B'));

$namespace->addUse('Bar\C');
Assert::same('C', $namespace->simplifyName('Foo\C'));

Assert::same('\Bar', $namespace->simplifyName('Bar'));
Assert::same('C', $namespace->simplifyName('\bar\C'));
Assert::same('C', $namespace->simplifyName('bar\C'));
Assert::same('C\D', $namespace->simplifyName('Bar\C\D'));
Assert::same('A<C, C\D>', $namespace->simplifyType('foo\A<\bar\C, Bar\C\D>'));
Assert::same('žluťoučký', $namespace->simplifyType('foo\žluťoučký'));


// duplicity
$namespace = new PhpNamespace('Foo');
$namespace->addUse('Bar\C');

Assert::exception(function () use ($namespace) {
	$namespace->addTrait('C');
}, Nette\InvalidStateException::class, "Name 'C' used already as alias for Bar\\C.");

$namespace->addClass('B');
Assert::exception(function () use ($namespace) {
	$namespace->addUse('Lorem\B', 'B');
}, Nette\InvalidStateException::class, "Name 'B' used already for 'Foo\\B'.");

Assert::same(['C' => 'Bar\\C'], $namespace->getUses());


// alias generation
$namespace = new PhpNamespace('');
$namespace->addUse('C');
Assert::same('C', $namespace->simplifyName('C'));
$namespace->addUse('Bar\C');
Assert::same('C1', $namespace->simplifyName('Bar\C'));

$namespace = new PhpNamespace('');
$namespace->addUse('Bar\C');
$namespace->addUse('C');
Assert::same('C1', $namespace->simplifyName('C'));

$namespace = new PhpNamespace('');
$namespace->addUse('A');
Assert::same('A', $namespace->simplifyName('A'));
$namespace->addUse('Bar\A');
Assert::same('A1', $namespace->simplifyName('Bar\A'));

$namespace = new PhpNamespace('Foo');
$namespace->addUse('C');
Assert::same('C', $namespace->simplifyName('C'));
$namespace->addUse('Bar\C');
Assert::same('C1', $namespace->simplifyName('Bar\C'));
Assert::same('C', $namespace->simplifyName('Foo\C'));
$namespace->addUse('Foo\C');
Assert::same('C', $namespace->simplifyName('Foo\C'));

$namespace = new PhpNamespace('Foo');
$namespace->addUse('Bar\C');
$namespace->addUse('C');
Assert::same('C1', $namespace->simplifyName('C'));
