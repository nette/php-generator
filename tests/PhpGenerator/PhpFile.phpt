<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

use Nette\PhpGenerator\PhpFile;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$file = new PhpFile;


$file->addDocument('This file is auto-generated. DO NOT EDIT!');
$file->addDocument('Hey there, I\'m here to document things.');


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


Assert::matchFile(__DIR__ . '/PhpFile.regular.expect', (string) $file);

$file->addClass('H');

$file->addClass('FooBar\\I');

Assert::matchFile(__DIR__ . '/PhpFile.bracketed.expect', (string) $file);
