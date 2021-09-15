<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/bodies.php';


Assert::exception(function () {
	ClassType::from(PDO::class, withBodies: true);
}, Nette\InvalidStateException::class, 'Source code of PDO not found.');


Assert::exception(function () {
	ClassType::from(new class {
		public function f()
		{
		}
	}, withBodies: true);
}, Nette\NotSupportedException::class, 'The $withBodies parameter cannot be used for anonymous functions.');


$res = ClassType::from(Abc\Class7::class, withBodies: true);
sameFile(__DIR__ . '/expected/ClassType.from.bodies.expect', (string) $res);
