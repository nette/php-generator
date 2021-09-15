<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/bodies.php';


Assert::exception(function () {
	ClassType::withBodiesFrom(PDO::class);
}, Nette\InvalidStateException::class, 'Source code of PDO not found.');


Assert::exception(function () {
	ClassType::withBodiesFrom(new class {
		public function f()
		{
		}
	});
}, Nette\NotSupportedException::class, 'The $withBodies parameter cannot be used for anonymous functions.');


$res = ClassType::withBodiesFrom(Abc\Class7::class);
sameFile(__DIR__ . '/expected/ClassType.from.bodies.expect', (string) $res);
