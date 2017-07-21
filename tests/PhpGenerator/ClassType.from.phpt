<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php';

$res[] = ClassType::from(Abc\Interface1::class);
$res[] = ClassType::from(Abc\Interface2::class);
$res[] = ClassType::from(Abc\Class1::class);
$res[] = ClassType::from(new \ReflectionClass(Abc\Class2::class));
$obj = new Abc\Class3;
$obj->prop2 = 1;
$res[] = ClassType::from(new \ReflectionObject($obj));

Assert::matchFile(__DIR__ . '/ClassType.from.expect', implode("\n", $res));
