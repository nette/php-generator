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

$fragmentFoo = $file->addFragment("Foo");
$classA = $fragmentFoo->addClass("A");
$interfaceB = $fragmentFoo->addInterface("B");
$traitC = $fragmentFoo->addTrait("C");

$classA
	->addImplement("Foo\\A")
	->addTrait("Foo\\C")
	->addImplement("Bar\\C")
	->addTrait("Bar\\D");

$fragmentBar = $file->addFragment("Bar");
$classB = $fragmentBar->addClass("B");
$interfaceC = $fragmentBar->addInterface("C");
$traitD = $fragmentBar->addTrait("D");

$classB
	->addExtend("Foo\\A")
	->addImplement("Foo\\B")
	->addTrait("Foo\\C");

$classE = $file->addClass("Baz\\E");
$interfaceF = $file->addInterface("Baz\\F");
$interfaceF
	->addExtend("Foo\\B")
	->addExtend("Bar\\C");
$traitG = $file->addTrait("Baz\\G");

Assert::matchFile(__DIR__ . "/PhpFile.regular.expect", (string)$file);

$file->addClass("H");

$file->addClass("FooBar\\I");

Assert::matchFile(__DIR__ . "/PhpFile.bracketed.expect", (string)$file);
