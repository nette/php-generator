<?php

/**
 * Test: Nette\PhpGenerator for configuring method spaces.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('A');
$class->addMethod('foo');
$class->addMethod('bar');
$class->setMethodSpacing(1);

Assert::matchFile(__DIR__ . '/ClassType.methodSpaces.expect', (string) $class);
