<?php

/**
 * Test: Nette\PhpGenerator generator.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/traits.php';


$res[] = ClassType::from('Trait1');
$res[] = ClassType::from('Trait2');
$res[] = ClassType::from('Class1');
$res[] = ClassType::from('Class2');
$res[] = ClassType::from('Class3');
$res[] = ClassType::from('Class4');
$res[] = ClassType::from('Class5');

sameFile(__DIR__ . '/expected/ClassType.from.trait.expect', implode("\n", $res));


$res = [];
$res[] = ClassType::withBodiesFrom('Trait1');
$res[] = ClassType::withBodiesFrom('Trait2');
$res[] = ClassType::withBodiesFrom('Class1');
$res[] = ClassType::withBodiesFrom('Class2');
$res[] = ClassType::withBodiesFrom('Class3');
$res[] = ClassType::withBodiesFrom('Class4');
$res[] = ClassType::withBodiesFrom('Class5');

sameFile(__DIR__ . '/expected/ClassType.from.trait.bodies.expect', implode("\n", $res));
