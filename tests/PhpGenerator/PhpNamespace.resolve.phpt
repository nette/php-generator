<?php declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


test('resolveName in global namespace', function () {
	$namespace = new PhpNamespace('');

	Assert::same('', $namespace->getName());
	Assert::same('', $namespace->resolveName(''));
	Assert::same('', $namespace->resolveName('\\'));
	Assert::same('A', $namespace->resolveName('A'));
	Assert::same('A', $namespace->resolveName('\A'));
	Assert::same('foo\A', $namespace->resolveName('foo\A'));
});


test('resolveName in global namespace with use statements', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');

	Assert::same('Bar', $namespace->resolveName('Bar'));
	Assert::same('Bar\C', $namespace->resolveName('c'));
	Assert::same('Bar\C\D', $namespace->resolveName('C\D'));
});


test('resolveName for functions in global namespace', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');

	Assert::same('bar\c', $namespace->resolveName('bar\c', $namespace::NameFunction));
	Assert::same('Foo\a', $namespace->resolveName('A', $namespace::NameFunction));
	Assert::same('foo\a\b', $namespace->resolveName('foo\a\b', $namespace::NameFunction));

	$namespace->addUseFunction('Bar\c');

	Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NameFunction));
	Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NameFunction));
	Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NameFunction));
});


test('resolveName for constants in global namespace', function () {
	$namespace = new PhpNamespace('');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');
	$namespace->addUseFunction('Bar\c');
	$namespace->addUseConstant('Bar\c');

	Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NameConstant));
	Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NameConstant));
	Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NameConstant));
});

test('resolveName preserves built-in types in namespace', function () {
	$namespace = new PhpNamespace('Foo');

	foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
		Assert::same($type, $namespace->resolveName($type));
	}
});


test('resolveName in namespace', function () {
	$namespace = new PhpNamespace('Foo');

	Assert::same('Foo', $namespace->getName());
	Assert::same('', $namespace->resolveName(''));
	Assert::same('', $namespace->resolveName('\\'));
	Assert::same('A', $namespace->resolveName('\A'));
	Assert::same('Foo\A', $namespace->resolveName('A'));
});


test('resolveName in namespace with use statements', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Foo');
	Assert::same('Foo\B', $namespace->resolveName('B'));

	$namespace->addUse('Bar\C');
	Assert::same('Foo\C', $namespace->resolveName('Foo\C'));

	Assert::same('Bar', $namespace->resolveName('\Bar'));
	Assert::same('Bar\C', $namespace->resolveName('C'));
	Assert::same('Bar\C', $namespace->resolveName('c'));
	Assert::same('Bar\C\D', $namespace->resolveName('c\D'));
});


test('resolveName for functions in namespace', function () {
	$namespace = new PhpNamespace('Foo');
	$namespace->addUse('Foo');
	$namespace->addUse('Bar\C');
	$namespace->addUseFunction('Foo\a');

	foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
		Assert::same($type, $namespace->resolveName($type, $namespace::NameFunction));
	}

	Assert::same('bar\c', $namespace->resolveName('\bar\c', $namespace::NameFunction));
	Assert::same('Foo\a', $namespace->resolveName('A', $namespace::NameFunction));
	Assert::same('Foo\C\b', $namespace->resolveName('foo\C\b', $namespace::NameFunction));
	Assert::same('Foo\A\b', $namespace->resolveName('A\b', $namespace::NameFunction));

	$namespace->addUseFunction('Bar\c');

	Assert::same('Bar', $namespace->resolveName('\Bar', $namespace::NameFunction));
	Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NameFunction));
	Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NameFunction));
});
