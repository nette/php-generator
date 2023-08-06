<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');

$class->addConstant('A', 10);
$class->addProperty('a');
$class->addMethod('a');

$dolly = clone $class;

Assert::notSame($dolly->getConstants(), $class->getConstants());
Assert::notSame($dolly->getProperty('a'), $class->getProperty('a'));
Assert::notSame($dolly->getMethod('a'), $class->getMethod('a'));
