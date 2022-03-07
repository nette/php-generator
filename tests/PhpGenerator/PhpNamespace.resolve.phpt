<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// global namespace
$namespace = new PhpNamespace('');

Assert::same('', $namespace->getName());
Assert::same('', $namespace->resolveName(''));
Assert::same('', $namespace->resolveName('\\'));
Assert::same('A', $namespace->resolveName('A'));
Assert::same('A', $namespace->resolveName('\A'));
Assert::same('foo\A', $namespace->resolveName('foo\A'));

$namespace->addUse('Bar\C');

Assert::same('Bar', $namespace->resolveName('Bar'));
Assert::same('Bar\C', $namespace->resolveName('c'));
Assert::same('Bar\C\D', $namespace->resolveName('C\D'));

$namespace->addUseFunction('Foo\a');

Assert::same('bar\c', $namespace->resolveName('bar\c', $namespace::NameFunction));
Assert::same('Foo\a', $namespace->resolveName('A', $namespace::NameFunction));
Assert::same('foo\a\b', $namespace->resolveName('foo\a\b', $namespace::NameFunction));

$namespace->addUseFunction('Bar\c');

Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NameFunction));
Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NameFunction));
Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NameFunction));

$namespace->addUseConstant('Bar\c');

Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NameConstant));
Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NameConstant));
Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NameConstant));



// namespace
$namespace = new PhpNamespace('Foo');

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->resolveName($type));
}

Assert::same('Foo', $namespace->getName());
Assert::same('', $namespace->resolveName(''));
Assert::same('', $namespace->resolveName('\\'));
Assert::same('A', $namespace->resolveName('\A'));
Assert::same('Foo\A', $namespace->resolveName('A'));

$namespace->addUse('Foo');
Assert::same('Foo\B', $namespace->resolveName('B'));

$namespace->addUse('Bar\C');
Assert::same('Foo\C', $namespace->resolveName('Foo\C'));

Assert::same('Bar', $namespace->resolveName('\Bar'));
Assert::same('Bar\C', $namespace->resolveName('C'));
Assert::same('Bar\C', $namespace->resolveName('c'));
Assert::same('Bar\C\D', $namespace->resolveName('c\D'));

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
