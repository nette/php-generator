<?php declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


test('simplifyName in global namespace', function () {
	$namespace = new PhpNamespace('');

	Assert::same('', $namespace->getName());
	Assert::same('A', $namespace->simplifyName('A'));
	Assert::same('foo\A', $namespace->simplifyName('foo\A'));
});


test('simplifyName in global namespace with use statements', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');

	Assert::same('Bar', $namespace->simplifyName('Bar'));
	Assert::same('C', $namespace->simplifyName('bar\C'));
	Assert::same('C\D', $namespace->simplifyName('Bar\C\D'));
});


test('simplifyName for functions in global namespace', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');

	Assert::same('bar\c', $namespace->simplifyName('bar\c', $namespace::NameFunction));
	Assert::same('a', $namespace->simplifyName('foo\A', $namespace::NameFunction));
	Assert::same('foo\a\b', $namespace->simplifyName('foo\a\b', $namespace::NameFunction));

	$namespace->addUseFunction('Bar\c');

	Assert::same('Bar', $namespace->simplifyName('Bar', $namespace::NameFunction));
	Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NameFunction));
	Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NameFunction));
});


test('simplifyName for constants in global namespace', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');
	$namespace->addUseFunction('Bar\c');
	$namespace->addUseConstant('Bar\c');

	Assert::same('Bar', $namespace->simplifyName('Bar', $namespace::NameConstant));
	Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NameConstant));
	Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NameConstant));
});

test('simplifyName preserves built-in types in namespace', function () {
	$namespace = new PhpNamespace('Foo');

	foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
		Assert::same($type, $namespace->simplifyName($type));
	}
});


test('simplifyName in namespace', function () {
	$namespace = new PhpNamespace('Foo');

	Assert::same('Foo', $namespace->getName());
	Assert::same('\A', $namespace->simplifyName('\A'));
	Assert::same('\A', $namespace->simplifyName('A'));
	Assert::same('A', $namespace->simplifyName('foo\A'));
});


test('simplifyType handles complex types', function () {
	$namespace = new PhpNamespace('Foo');

	Assert::same('A', $namespace->simplifyType('foo\A'));
	Assert::same('null|A', $namespace->simplifyType('null|foo\A'));
	Assert::same('?A', $namespace->simplifyType('?foo\A'));
	Assert::same('A&\Countable', $namespace->simplifyType('foo\A&Countable'));
	Assert::same('', $namespace->simplifyType(''));
});


test('simplifyName in namespace with use statements', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Foo');
	Assert::same('B', $namespace->simplifyName('Foo\B'));

	$namespace->addUse('Bar\C');
	Assert::same('Foo\C', $namespace->simplifyName('Foo\C'));

	Assert::same('\Bar', $namespace->simplifyName('Bar'));
	Assert::same('C', $namespace->simplifyName('\bar\C'));
	Assert::same('C', $namespace->simplifyName('bar\C'));
	Assert::same('C\D', $namespace->simplifyName('Bar\C\D'));
	Assert::same('A<C, C\D>', $namespace->simplifyType('foo\A<\bar\C, Bar\C\D>'));
	Assert::same('žluťoučký', $namespace->simplifyType('foo\žluťoučký'));
});


test('simplifyName for functions in namespace', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Foo');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');

	foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
		Assert::same($type, $namespace->simplifyName($type, $namespace::NameFunction));
	}

	Assert::same('\bar\c', $namespace->simplifyName('bar\c', $namespace::NameFunction));
	Assert::same('a', $namespace->simplifyName('foo\A', $namespace::NameFunction));
	Assert::same('Foo\C\b', $namespace->simplifyName('foo\C\b', $namespace::NameFunction));
	Assert::same('a\b', $namespace->simplifyName('foo\a\b', $namespace::NameFunction));

	$namespace->addUseFunction('Bar\c');

	Assert::same('\Bar', $namespace->simplifyName('Bar', $namespace::NameFunction));
	Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NameFunction));
	Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NameFunction));
});
