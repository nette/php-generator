<?php

/**
 * Test: empty namespaces.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
require __DIR__ . '/../bootstrap.php';


// no namespace
$class = new ClassType('Example');
$class
	->setExtends('\ParentClass')
	->addImplement('One')
	->addImplement('\Two');

$class->addTrait('Three');
$class->addTrait('\Four');

$class->addMethod('one')
	->setReturnType('One');

$method = $class->addMethod('two')
	->setReturnType('\Two');

$method->addParameter('one')
		->setType('One');

$method->addParameter('two')
		->setType('\Two');

sameFile(__DIR__ . '/expected/PhpNamespace.fqn1.expect', (string) $class);

sameFile(__DIR__ . '/expected/PhpNamespace.fqn1.expect', (new Printer)->printClass($class));



// global namespace
$class = new ClassType('Example', new PhpNamespace(''));
$class
	->setExtends('\ParentClass')
	->addImplement('One')
	->addImplement('\Two');

$class->addTrait('Three');
$class->addTrait('\Four');

$class->addMethod('one')
	->setReturnType('One');

$method = $class->addMethod('two')
	->setReturnType('\Two');

$method->addParameter('one')
		->setType('One');

$method->addParameter('two')
		->setType('\Two');

sameFile(__DIR__ . '/expected/PhpNamespace.fqn2.expect', (string) $class);

sameFile(__DIR__ . '/expected/PhpNamespace.fqn2.expect', (new Printer)->printClass($class, new PhpNamespace('')));

// no resolve
sameFile(__DIR__ . '/expected/PhpNamespace.fqn1.expect', (new Printer)->printClass($class));
