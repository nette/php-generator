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

Assert::same('bar\c', $namespace->resolveName('bar\c', $namespace::NAME_FUNCTION));
Assert::same('Foo\a', $namespace->resolveName('A', $namespace::NAME_FUNCTION));
Assert::same('foo\a\b', $namespace->resolveName('foo\a\b', $namespace::NAME_FUNCTION));

$namespace->addUseFunction('Bar\c');

Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NAME_FUNCTION));
Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NAME_FUNCTION));
Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NAME_FUNCTION));

$namespace->addUseConstant('Bar\c');

Assert::same('Bar', $namespace->resolveName('Bar', $namespace::NAME_CONSTANT));
Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NAME_CONSTANT));
Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NAME_CONSTANT));



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
	Assert::same($type, $namespace->resolveName($type, $namespace::NAME_FUNCTION));
}

Assert::same('bar\c', $namespace->resolveName('\bar\c', $namespace::NAME_FUNCTION));
Assert::same('Foo\a', $namespace->resolveName('A', $namespace::NAME_FUNCTION));
Assert::same('Foo\C\b', $namespace->resolveName('foo\C\b', $namespace::NAME_FUNCTION));
Assert::same('Foo\A\b', $namespace->resolveName('A\b', $namespace::NAME_FUNCTION));

$namespace->addUseFunction('Bar\c');

Assert::same('Bar', $namespace->resolveName('\Bar', $namespace::NAME_FUNCTION));
Assert::same('Bar\c', $namespace->resolveName('C', $namespace::NAME_FUNCTION));
Assert::same('Bar\C\d', $namespace->resolveName('c\d', $namespace::NAME_FUNCTION));
