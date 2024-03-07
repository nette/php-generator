<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\TraitType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php';


Assert::error(
	fn() => ClassType::from(Abc\Interface1::class),
	E_USER_WARNING,
	'Abc\Interface1 cannot be represented with Nette\PhpGenerator\ClassType. Call Nette\PhpGenerator\InterfaceType::from() or Nette\PhpGenerator\ClassLike::from() instead.',
);

Assert::error(
	fn() => TraitType::from(Abc\Class1::class),
	E_USER_WARNING,
	'Abc\Class1 cannot be represented with Nette\PhpGenerator\TraitType. Call Nette\PhpGenerator\ClassType::from() or Nette\PhpGenerator\ClassLike::from() instead.',
);

Assert::error(
	fn() => ClassType::fromCode('<?php interface I {}'),
	E_USER_WARNING,
	'Provided code cannot be represented with Nette\PhpGenerator\ClassType. Call Nette\PhpGenerator\InterfaceType::fromCode() or Nette\PhpGenerator\ClassLike::fromCode() instead.',
);

Assert::error(
	fn() => InterfaceType::fromCode('<?php trait T {}'),
	E_USER_WARNING,
	'Provided code cannot be represented with Nette\PhpGenerator\InterfaceType. Call Nette\PhpGenerator\TraitType::fromCode() or Nette\PhpGenerator\ClassLike::fromCode() instead.',
);
