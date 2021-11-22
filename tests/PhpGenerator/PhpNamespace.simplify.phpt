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

$namespace->addUseFunction('Foo\a');

Assert::same('bar\c', $namespace->simplifyName('bar\c', $namespace::NAME_FUNCTION));
Assert::same('a', $namespace->simplifyName('foo\A', $namespace::NAME_FUNCTION));
Assert::same('foo\a\b', $namespace->simplifyName('foo\a\b', $namespace::NAME_FUNCTION));

$namespace->addUseFunction('Bar\c');

Assert::same('Bar', $namespace->simplifyName('Bar', $namespace::NAME_FUNCTION));
Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NAME_FUNCTION));
Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NAME_FUNCTION));

$namespace->addUseConstant('Bar\c');

Assert::same('Bar', $namespace->simplifyName('Bar', $namespace::NAME_CONSTANT));
Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NAME_CONSTANT));
Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NAME_CONSTANT));



// namespace
$namespace = new PhpNamespace('Foo');

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->simplifyName($type));
}

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
Assert::same('Foo\C', $namespace->simplifyName('Foo\C'));

Assert::same('\Bar', $namespace->simplifyName('Bar'));
Assert::same('C', $namespace->simplifyName('\bar\C'));
Assert::same('C', $namespace->simplifyName('bar\C'));
Assert::same('C\D', $namespace->simplifyName('Bar\C\D'));
Assert::same('A<C, C\D>', $namespace->simplifyType('foo\A<\bar\C, Bar\C\D>'));
Assert::same('žluťoučký', $namespace->simplifyType('foo\žluťoučký'));

$namespace->addUseFunction('Foo\a');

foreach (['String', 'string', 'int', 'float', 'bool', 'array', 'callable', 'self', 'parent', ''] as $type) {
	Assert::same($type, $namespace->simplifyName($type, $namespace::NAME_FUNCTION));
}

Assert::same('\bar\c', $namespace->simplifyName('bar\c', $namespace::NAME_FUNCTION));
Assert::same('a', $namespace->simplifyName('foo\A', $namespace::NAME_FUNCTION));
Assert::same('Foo\C\b', $namespace->simplifyName('foo\C\b', $namespace::NAME_FUNCTION));
Assert::same('a\b', $namespace->simplifyName('foo\a\b', $namespace::NAME_FUNCTION));

$namespace->addUseFunction('Bar\c');

Assert::same('\Bar', $namespace->simplifyName('Bar', $namespace::NAME_FUNCTION));
Assert::same('c', $namespace->simplifyName('bar\c', $namespace::NAME_FUNCTION));
Assert::same('C\d', $namespace->simplifyName('Bar\C\d', $namespace::NAME_FUNCTION));
