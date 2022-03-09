<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// duplicity
$namespace = new PhpNamespace('Foo');
$namespace->addUse('Bar\C');

Assert::exception(function () use ($namespace) {
	$namespace->addTrait('C');
}, Nette\InvalidStateException::class, "Name 'C' used already as alias for Bar\\C.");

Assert::exception(function () use ($namespace) {
	$namespace->addTrait('c');
}, Nette\InvalidStateException::class, "Name 'c' used already as alias for Bar\\C.");

$namespace->addClass('B');
Assert::exception(function () use ($namespace) {
	$namespace->addUse('Lorem\B', 'B');
}, Nette\InvalidStateException::class, "Name 'B' used already for 'Foo\\B'.");

Assert::exception(function () use ($namespace) {
	$namespace->addUse('lorem\b', 'b');
}, Nette\InvalidStateException::class, "Name 'b' used already for 'Foo\\B'.");

$namespace->addUseFunction('Bar\f1');
Assert::exception(function () use ($namespace) {
	$namespace->addFunction('f1');
}, Nette\InvalidStateException::class, "Name 'f1' used already as alias for Bar\\f1.");

Assert::exception(function () use ($namespace) {
	$namespace->addFunction('F1');
}, Nette\InvalidStateException::class, "Name 'F1' used already as alias for Bar\\f1.");

$namespace->addFunction('f2');
Assert::exception(function () use ($namespace) {
	$namespace->addUseFunction('Bar\f2', 'f2');
}, Nette\InvalidStateException::class, "Name 'f2' used already for 'Foo\\f2'.");

Assert::exception(function () use ($namespace) {
	$namespace->addUseFunction('Bar\f2', 'F2');
}, Nette\InvalidStateException::class, "Name 'F2' used already for 'Foo\\f2'.");

Assert::same(['C' => 'Bar\C'], $namespace->getUses());
Assert::same(['f1' => 'Bar\f1'], $namespace->getUses($namespace::NameFunction));


// alias generation
$namespace = new PhpNamespace('');
$namespace->addUse('C');
Assert::same('C', $namespace->simplifyName('C'));
$namespace->addUse('Bar\C');
Assert::same('C1', $namespace->simplifyName('Bar\C'));
$namespace->removeUse('bar\c');
Assert::same('Bar\C', $namespace->simplifyName('Bar\C'));

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
Assert::same('\Foo\C', $namespace->simplifyName('Foo\C'));
$namespace->addUse('Foo\C');
Assert::same('C2', $namespace->simplifyName('Foo\C'));

$namespace = new PhpNamespace('Foo');
$namespace->addUse('Bar\C');
$namespace->addUse('C');
Assert::same('C1', $namespace->simplifyName('C'));

$namespace = new PhpNamespace('Foo\Bar');
$namespace->addUse('Foo\Bar\Baz\Qux');
Assert::same('Qux', $namespace->simplifyName('Foo\Bar\Baz\Qux'));

$namespace = new PhpNamespace('Foo');
$namespace->addUseFunction('Bar\c');
$namespace->addUseFunction('c');
Assert::same('c1', $namespace->simplifyName('c', $namespace::NameFunction));
$namespace->removeUse('c', $namespace::NameFunction);
Assert::same('\c', $namespace->simplifyName('c', $namespace::NameFunction));
