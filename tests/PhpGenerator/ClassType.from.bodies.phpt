<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/bodies.php';


Assert::exception(
	fn() => ClassType::from(PDO::class, withBodies: true),
	Nette\NotSupportedException::class,
	'The $withBodies parameter cannot be used for anonymous or internal classes or interfaces.',
);


Assert::exception(
	fn() => ClassType::from(new class {
		public function f()
		{
		}
	}, withBodies: true),
	Nette\NotSupportedException::class,
	'The $withBodies parameter cannot be used for anonymous or internal classes or interfaces.',
);


$res = ClassType::from(Abc\Class7::class, withBodies: true);
sameFile(__DIR__ . '/expected/ClassType.from.bodies.expect', (string) $res);


if (PHP_VERSION_ID >= 80400) {
	require __DIR__ . '/fixtures/classes.84.php';
	$res = [];
	$res[] = ClassType::from(Abc\PropertyHookSignatures::class, withBodies: true);
	$res[] = ClassType::from(Abc\AbstractHookSignatures::class, withBodies: true);
	$res[] = ClassType::from(Abc\PropertyHookSignaturesChild::class, withBodies: true);
	sameFile(__DIR__ . '/expected/ClassType.from.bodies.84.expect', implode("\n", $res));
}
