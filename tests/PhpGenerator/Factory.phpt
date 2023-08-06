<?php

/**
 * Test: Nette\PhpGenerator\Factory
 */

declare(strict_types=1);

use Nette\PhpGenerator\Factory;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$factory = new Factory;

$res = $factory->fromClassReflection(new ReflectionClass(stdClass::class));
Assert::type(Nette\PhpGenerator\ClassType::class, $res);
Assert::same('stdClass', $res->getName());


$res = $factory->fromClassReflection(new ReflectionClass(new class {
}));
Assert::type(Nette\PhpGenerator\ClassType::class, $res);
Assert::null($res->getName());


$res = $factory->fromMethodReflection(new ReflectionMethod(ReflectionClass::class, 'getName'));
Assert::type(Nette\PhpGenerator\Method::class, $res);
Assert::same('getName', $res->getName());


$res = $factory->fromFunctionReflection(new ReflectionFunction('trim'));
Assert::type(Nette\PhpGenerator\GlobalFunction::class, $res);
Assert::same('trim', $res->getName());


$res = $factory->fromFunctionReflection(new ReflectionFunction(function () {}));
Assert::type(Nette\PhpGenerator\Closure::class, $res);


$res = $factory->fromCallable('trim');
Assert::type(Nette\PhpGenerator\GlobalFunction::class, $res);
Assert::same('trim', $res->getName());


$res = $factory->fromCallable([new ReflectionClass(stdClass::class), 'getName']);
Assert::type(Nette\PhpGenerator\Method::class, $res);
Assert::same('getName', $res->getName());
