<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

declare(strict_types=1);

use Nette\PhpGenerator\PhpFile;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$file = new PhpFile;


$file->addComment('This file is auto-generated. DO NOT EDIT!');
$file->addComment('Hey there, I\'m here to document things.');


$namespaceFoo = $file->addNamespace('Foo');

$classA = $namespaceFoo->addClass('A');
Assert::same($namespaceFoo, $classA->getNamespace());

$interfaceB = $namespaceFoo->addInterface('B');
Assert::same($namespaceFoo, $interfaceB->getNamespace());

$traitC = $namespaceFoo->addTrait('C');
Assert::same($namespaceFoo, $traitC->getNamespace());

$classA
	->addImplement('Foo\A')
	->addTrait('Foo\C')
	->addImplement('Bar\C')
	->addTrait('Bar\D');


$namespaceBar = $file->addNamespace('Bar');

$classB = $namespaceBar->addClass('B');
Assert::same($classB->getNamespace(), $namespaceBar);

$interfaceC = $namespaceBar->addInterface('C');
Assert::same($interfaceC->getNamespace(), $namespaceBar);

$traitD = $namespaceBar->addTrait('D');
Assert::same($traitD->getNamespace(), $namespaceBar);

$enumEN = $namespaceBar->addEnum('EN');
Assert::same($enumEN->getNamespace(), $namespaceBar);

$classB
	->addExtend('Foo\A')
	->addImplement('Foo\B')
	->addTrait('Foo\C');


$classE = $file->addClass('Baz\E');
Assert::same($file->addNamespace('Baz'), $classE->getNamespace());

$interfaceF = $file->addInterface('Baz\F');
Assert::same($file->addNamespace('Baz'), $interfaceF->getNamespace());

$interfaceF
	->addExtend('Foo\B')
	->addExtend('Bar\C');

$traitG = $file->addTrait('Baz\G');
Assert::same($file->addNamespace('Baz'), $traitG->getNamespace());

$file->addFunction('Baz\\f2')
	->setReturnType('Foo\B');


sameFile(__DIR__ . '/expected/PhpFile.regular.expect', (string) $file);

$file->addClass('H');

$file->addClass('FooBar\I');

$file->addFunction('f1')
	->setBody('return 1;');

sameFile(__DIR__ . '/expected/PhpFile.bracketed.expect', (string) $file);

Assert::same([
	'Foo',
	'Bar',
	'Baz',
	'',
	'FooBar',
], array_keys($file->getNamespaces()));

Assert::same([
	'Foo\A',
	'Foo\B',
	'Foo\C',
	'Bar\B',
	'Bar\C',
	'Bar\D',
	'Bar\EN',
	'Baz\E',
	'Baz\F',
	'Baz\G',
	'H',
	'FooBar\I',
], array_keys($file->getClasses()));

Assert::same(['Baz\\f2', 'f1'], array_keys($file->getFunctions()));




$file = new PhpFile;
$file->addClass('CA');
$file->addUse('A')
	->addUse('B', 'C');

sameFile(__DIR__ . '/expected/PhpFile.globalNamespace.expect', (string) $file);



$file = new PhpFile;
$file->addComment('This file is auto-generated. DO NOT EDIT!');
$file->setStrictTypes();
$file->addClass('A');

sameFile(__DIR__ . '/expected/PhpFile.strictTypes.expect', (string) $file);



$file = PhpFile::fromCode(file_get_contents(__DIR__ . '/fixtures/classes.php'));
Assert::type(PhpFile::class, $file);
sameFile(__DIR__ . '/expected/Extractor.classes.expect', (string) $file);
