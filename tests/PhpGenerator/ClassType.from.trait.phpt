<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/traits.php';

$classes = [
	Trait1::class,
	Trait1b::class,
	Trait2::class,
	ParentClass::class,
	Class1::class,
	Class2::class,
	Class3::class,
	Class4::class,
	Class5::class,
];

$res = array_map(fn($class) => ClassType::from($class), $classes);

sameFile(__DIR__ . '/expected/ClassType.from.trait-use.expect', implode("\n", $res));


$res = array_map(fn($class) => ClassType::from($class, withBodies: true), $classes);

sameFile(__DIR__ . '/expected/ClassType.from.trait-use.bodies.expect', implode("\n", $res));
