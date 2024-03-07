<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\TraitType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php';

Assert::exception(function () {
	ClassType::from(Abc\Interface1::class);
}, Nette\InvalidArgumentException::class, "'Abc\\Interface1' cannot be represented with Nette\\PhpGenerator\\ClassType. Call Nette\\PhpGenerator\\InterfaceType::from() or Nette\\PhpGenerator\\ClassLike::from() instead.");

Assert::exception(function () {
    TraitType::from(Abc\Class1::class);
}, Nette\InvalidArgumentException::class, "'Abc\\Class1' cannot be represented with Nette\\PhpGenerator\\TraitType. Call Nette\\PhpGenerator\\ClassType::from() or Nette\\PhpGenerator\\ClassLike::from() instead.");

Assert::exception(function () {
    ClassType::fromCode("<?php interface I {}");
}, Nette\InvalidArgumentException::class, "Provided code cannot be represented with Nette\\PhpGenerator\\ClassType. Call Nette\\PhpGenerator\\InterfaceType::fromCode() or Nette\\PhpGenerator\\ClassLike::fromCode() instead.");

Assert::exception(function () {
    InterfaceType::fromCode("<?php trait T {}");
}, Nette\InvalidArgumentException::class, "Provided code cannot be represented with Nette\\PhpGenerator\\InterfaceType. Call Nette\\PhpGenerator\\TraitType::fromCode() or Nette\\PhpGenerator\\ClassLike::fromCode() instead.");
