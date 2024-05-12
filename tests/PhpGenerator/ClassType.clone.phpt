<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');

$class->addAttribute('Attr');
$class->addConstant('A', 10);
$class->addProperty('a');
$class->addMethod('a')
	->addParameter('foo');

$dolly = clone $class;

Assert::notSame($dolly->getAttributes(), $class->getAttributes());
Assert::notSame($dolly->getConstants(), $class->getConstants());
Assert::notSame($dolly->getProperty('a'), $class->getProperty('a'));
Assert::notSame($dolly->getMethod('a'), $class->getMethod('a'));
Assert::notSame($dolly->getMethod('a')->getParameter('foo'), $class->getMethod('a')->getParameter('foo'));
