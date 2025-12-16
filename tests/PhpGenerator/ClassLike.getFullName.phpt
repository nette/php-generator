<?php

/**
 * Test: ClassLike::getFullName()
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// class without namespace
$class = new ClassType('Demo');
Assert::same('Demo', $class->getFullName());


// class with namespace
$class = new ClassType('User', new PhpNamespace('App\Model'));
Assert::same('App\Model\User', $class->getFullName());


// class with empty namespace
$class = new ClassType('GlobalClass', new PhpNamespace(''));
Assert::same('GlobalClass', $class->getFullName());


// anonymous class (no name)
$class = new ClassType(null);
Assert::null($class->getFullName());
