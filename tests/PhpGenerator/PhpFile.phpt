<?php

/**
 * Test: Nette\PhpGenerator for files.
 */

use Nette\PhpGenerator\PhpFile;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$file = new PhpFile();


$file->addDocument("This file is auto-generated. DO NOT EDIT!");
$file->addDocument("Hey there, I'm here to document things.");


$namespaceFoo = $file->addNamespace("Foo");
Assert::same($file, $namespaceFoo->getFile());

$classA = $namespaceFoo->addClass("A");
Assert::same($namespaceFoo, $classA->getNamespace());
Assert::same($file, $classA->getNamespace()->getFile());

$interfaceB = $namespaceFoo->addInterface("B");
Assert::same($namespaceFoo, $interfaceB->getNamespace());
Assert::same($file, $interfaceB->getNamespace()->getFile());

$traitC = $namespaceFoo->addTrait("C");
Assert::same($namespaceFoo, $traitC->getNamespace());
Assert::same($file, $traitC->getNamespace()->getFile());

$classA
	->addImplement("Foo\\A")
	->addTrait("Foo\\C")
	->addImplement("Bar\\C")
	->addTrait("Bar\\D");


$namespaceBar = $file->addNamespace("Bar");
Assert::same($file, $namespaceBar->getFile());

$classB = $namespaceBar->addClass("B");
Assert::same($namespaceBar, $classB->getNamespace());
Assert::same($file, $classB->getNamespace()->getFile());

$interfaceC = $namespaceBar->addInterface("C");
Assert::same($namespaceBar, $interfaceC->getNamespace());
Assert::same($file, $interfaceC->getNamespace()->getFile());

$traitD = $namespaceBar->addTrait("D");
Assert::same($namespaceBar, $traitD->getNamespace());
Assert::same($file, $traitD->getNamespace()->getFile());

$classB
	->addExtend("Foo\\A")
	->addImplement("Foo\\B")
	->addTrait("Foo\\C");


$classE = $file->addClass("Baz\\E");
Assert::same($file->addNamespace("Baz"), $classE->getNamespace());
Assert::same($file, $classE->getNamespace()->getFile());

$interfaceF = $file->addInterface("Baz\\F");
Assert::same($file->addNamespace("Baz"), $interfaceF->getNamespace());
Assert::same($file, $interfaceF->getNamespace()->getFile());

$interfaceF
	->addExtend("Foo\\B")
	->addExtend("Bar\\C");

$traitG = $file->addTrait("Baz\\G");
Assert::same($file->addNamespace("Baz"), $traitG->getNamespace());
Assert::same($file, $traitG->getNamespace()->getFile());


Assert::matchFile(__DIR__ . "/PhpFile.regular.expect", (string)$file);

$file->addClass("H");

$file->addClass("FooBar\\I");

Assert::matchFile(__DIR__ . "/PhpFile.bracketed.expect", (string)$file);
