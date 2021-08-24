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
	->addImplement('Foo\\A')
	->addTrait('Foo\\C')
	->addImplement('Bar\\C')
	->addTrait('Bar\\D');


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
	->addExtend('Foo\\A')
	->addImplement('Foo\\B')
	->addTrait('Foo\\C');


$classE = $file->addClass('Baz\\E');
Assert::same($file->addNamespace('Baz'), $classE->getNamespace());

$interfaceF = $file->addInterface('Baz\\F');
Assert::same($file->addNamespace('Baz'), $interfaceF->getNamespace());

$interfaceF
	->addExtend('Foo\\B')
	->addExtend('Bar\\C');

$traitG = $file->addTrait('Baz\\G');
Assert::same($file->addNamespace('Baz'), $traitG->getNamespace());


sameFile(__DIR__ . '/expected/PhpFile.regular.expect', (string) $file);

$file->addClass('H');

$file->addClass('FooBar\\I');

sameFile(__DIR__ . '/expected/PhpFile.bracketed.expect', (string) $file);

$file = new PhpFile;
$file->addClass('A');
$file->addUse('A')
	->addUse('B', 'C');

sameFile(__DIR__ . '/expected/PhpFile.globalNamespace.expect', (string) $file);

$file = new PhpFile;
$file->addComment('This file is auto-generated. DO NOT EDIT!');
$file->setStrictTypes();
$file->addClass('A');

sameFile(__DIR__ . '/expected/PhpFile.strictTypes.expect', (string) $file);
