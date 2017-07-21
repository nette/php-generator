<?php

/**
 * Test: Nette\PhpGenerator generator.
 * @phpversion 7.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php71.php';


$res[] = ClassType::from(ClassA::class);
$res[] = ClassType::from(ClassB::class);

Assert::matchFile(__DIR__ . '/ClassType.from.php71.expect', implode("\n", $res));
