<?php

/**
 * Test: Nette\PhpGenerator\Factory
 */

use Nette\PhpGenerator\Factory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$factory = new Factory;

$res = $factory->fromClassReflection(new ReflectionClass(stdClass::class));
Assert::type(Nette\PhpGenerator\ClassType::class, $res);
Assert::same('stdClass', $res->getName());


$res = $factory->fromMethodReflection(new \ReflectionMethod(ReflectionClass::class, 'getName'));
Assert::type(Nette\PhpGenerator\Method::class, $res);
Assert::same('getName', $res->getName());


$res = $factory->fromFunctionReflection(new \ReflectionFunction('trim'));
Assert::type(Nette\PhpGenerator\GlobalFunction::class, $res);
Assert::same('trim', $res->getName());


$res = $factory->fromFunctionReflection(new \ReflectionFunction(function () {}));
Assert::type(Nette\PhpGenerator\Closure::class, $res);
