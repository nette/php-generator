<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Factory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php';

$res[] = ClassType::from(Abc\Interface1::class);
$res[] = ClassType::from(Abc\Interface2::class);
$res[] = ClassType::from(Abc\Interface3::class);
$res[] = ClassType::from(Abc\Interface4::class);
$res[] = ClassType::from(Abc\Class1::class);
$res[] = ClassType::from(new Abc\Class2);
$obj = new Abc\Class3;
@$obj->prop2 = 1; // dynamic property
$res[] = (new Factory)->fromClassReflection(new ReflectionObject($obj));
$res[] = ClassType::from(Abc\Class4::class);
$res[] = ClassType::from(Abc\Class5::class);
$res[] = ClassType::from(Abc\Class6::class);
$res[] = ClassType::from(Abc\Class7::class);
$res[] = ClassType::from(Abc\Class8::class);
$res[] = ClassType::from(Abc\Class9::class);
$res[] = ClassType::from(Abc\Class10::class);

sameFile(__DIR__ . '/expected/ClassType.from.expect', implode("\n", $res));
