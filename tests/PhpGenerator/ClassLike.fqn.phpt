<?php

/**
 * Test: ClassLike with FQN in constructor
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\TraitType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// ClassType with FQN
$class = new ClassType('App\Model\User');
Assert::same('User', $class->getName());
Assert::same('App\Model', $class->getNamespace()->getName());


// InterfaceType with FQN
$interface = new InterfaceType('App\Contracts\Countable');
Assert::same('Countable', $interface->getName());
Assert::same('App\Contracts', $interface->getNamespace()->getName());


// TraitType with FQN
$trait = new TraitType('App\Traits\Timestampable');
Assert::same('Timestampable', $trait->getName());
Assert::same('App\Traits', $trait->getNamespace()->getName());


// EnumType with FQN
$enum = new EnumType('App\Enums\Status');
Assert::same('Status', $enum->getName());
Assert::same('App\Enums', $enum->getNamespace()->getName());


// Single level namespace
$class = new ClassType('Nette\Utils');
Assert::same('Utils', $class->getName());
Assert::same('Nette', $class->getNamespace()->getName());


// No namespace
$class = new ClassType('User');
Assert::same('User', $class->getName());
Assert::null($class->getNamespace());


// FQN namespace has precedence over explicit namespace parameter
$class = new ClassType('App\Model\User', new PhpNamespace('Explicit\Namespace'));
Assert::same('User', $class->getName());
Assert::same('App\Model', $class->getNamespace()->getName());
