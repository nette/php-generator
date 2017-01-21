<?php

/**
 * Test: Nette\PhpGenerator\Factory
 * @phpversion 7.0
 */

declare(strict_types=1);

use Nette\PhpGenerator\Factory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$factory = new Factory;

$res = $factory->fromClassReflection(new ReflectionClass(new class {}));
Assert::type(Nette\PhpGenerator\ClassType::class, $res);
Assert::null($res->getName());
