<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


test('adding trait with name conflicting with use alias throws exception', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Bar\C');

	Assert::exception(
		fn() => $namespace->addTrait('C'),
		Nette\InvalidStateException::class,
		"Name 'C' used already as alias for Bar\\C.",
	);

	Assert::exception(
		fn() => $namespace->addTrait('c'),
		Nette\InvalidStateException::class,
		"Name 'c' used already as alias for Bar\\C.",
	);
});


test('adding use alias conflicting with existing class throws exception', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addClass('B');

	Assert::exception(
		fn() => $namespace->addUse('Lorem\B', 'B'),
		Nette\InvalidStateException::class,
		"Name 'B' used already for 'Foo\\B'.",
	);

	Assert::exception(
		fn() => $namespace->addUse('lorem\b', 'b'),
		Nette\InvalidStateException::class,
		"Name 'b' used already for 'Foo\\B'.",
	);
});


test('adding function with name conflicting with use function alias throws exception', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUseFunction('Bar\f1');

	Assert::exception(
		fn() => $namespace->addFunction('f1'),
		Nette\InvalidStateException::class,
		"Name 'f1' used already as alias for Bar\\f1.",
	);

	Assert::exception(
		fn() => $namespace->addFunction('F1'),
		Nette\InvalidStateException::class,
		"Name 'F1' used already as alias for Bar\\f1.",
	);
});


test('adding use function alias conflicting with existing function throws exception', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addFunction('f2');

	Assert::exception(
		fn() => $namespace->addUseFunction('Bar\f2', 'f2'),
		Nette\InvalidStateException::class,
		"Name 'f2' used already for 'Foo\\f2'.",
	);

	Assert::exception(
		fn() => $namespace->addUseFunction('Bar\f2', 'F2'),
		Nette\InvalidStateException::class,
		"Name 'F2' used already for 'Foo\\f2'.",
	);
});


test('getUses returns correct aliases for classes and functions', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Bar\f1');

	Assert::same(['C' => 'Bar\C'], $namespace->getUses());
	Assert::same(['f1' => 'Bar\f1'], $namespace->getUses($namespace::NameFunction));
});


test('automatic alias generation for conflicting use statements in global namespace', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('C');
	Assert::same('C', $namespace->simplifyName('C'));
	$namespace->addUse('Bar\C');
	Assert::same('C1', $namespace->simplifyName('Bar\C'));
	$namespace->removeUse('bar\c');
	Assert::same('Bar\C', $namespace->simplifyName('Bar\C'));
});


test('automatic alias generation when second use conflicts with first', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');
	$namespace->addUse('C');
	Assert::same('C1', $namespace->simplifyName('C'));
});


test('automatic alias generation for multiple uses', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('A');
	Assert::same('A', $namespace->simplifyName('A'));
	$namespace->addUse('Bar\A');
	Assert::same('A1', $namespace->simplifyName('Bar\A'));
});


test('automatic alias generation in non-global namespace', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('C');
	Assert::same('C', $namespace->simplifyName('C'));
	$namespace->addUse('Bar\C');
	Assert::same('C1', $namespace->simplifyName('Bar\C'));
	Assert::same('\Foo\C', $namespace->simplifyName('Foo\C'));
	$namespace->addUse('Foo\C');
	Assert::same('C2', $namespace->simplifyName('Foo\C'));
});


test('automatic alias generation when order is reversed', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Bar\C');
	$namespace->addUse('C');
	Assert::same('C1', $namespace->simplifyName('C'));
});


test('simplifyName for nested namespace', function () {
	$namespace = new PhpNamespace('Foo\Bar');
	$namespace->addUse('Foo\Bar\Baz\Qux');
	Assert::same('Qux', $namespace->simplifyName('Foo\Bar\Baz\Qux'));
});


test('automatic alias generation for functions', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUseFunction('Bar\c');
	$namespace->addUseFunction('c');
	Assert::same('c1', $namespace->simplifyName('c', $namespace::NameFunction));
	$namespace->removeUse('c', $namespace::NameFunction);
	Assert::same('\c', $namespace->simplifyName('c', $namespace::NameFunction));
});
