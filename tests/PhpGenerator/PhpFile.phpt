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
Assert::same($fragmentFoo, $classA->getFragment());

$interfaceB = $fragmentFoo->addInterface("B");
Assert::same($fragmentFoo, $interfaceB->getFragment());

$traitC = $fragmentFoo->addTrait("C");
Assert::same($fragmentFoo, $traitC->getFragment());

$classA
	->addImplement("Foo\\A")
	->addTrait("Foo\\C")
	->addImplement("Bar\\C")
	->addTrait("Bar\\D");


$fragmentBar = $file->addFragment("Bar");

$classB = $fragmentBar->addClass("B");
Assert::same($classB->getFragment(), $fragmentBar);

$interfaceC = $fragmentBar->addInterface("C");
Assert::same($interfaceC->getFragment(), $fragmentBar);

$traitD = $fragmentBar->addTrait("D");
Assert::same($traitD->getFragment(), $fragmentBar);

$classB
	->addExtend("Foo\\A")
	->addImplement("Foo\\B")
	->addTrait("Foo\\C");


$classE = $file->addClass("Baz\\E");
Assert::same($file->addFragment("Baz"), $classE->getFragment());

$interfaceF = $file->addInterface("Baz\\F");
Assert::same($file->addFragment("Baz"), $interfaceF->getFragment());

$interfaceF
	->addExtend("Foo\\B")
	->addExtend("Bar\\C");

$traitG = $file->addTrait("Baz\\G");
Assert::same($file->addFragment("Baz"), $traitG->getFragment());


Assert::matchFile(__DIR__ . "/PhpFile.regular.expect", (string)$file);

$file->addClass("H");

$file->addClass("FooBar\\I");

Assert::matchFile(__DIR__ . "/PhpFile.bracketed.expect", (string)$file);
