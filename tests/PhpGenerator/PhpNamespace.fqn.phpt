<?php

/**
 * Test: empty namespaces.
 */

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// no namespace
$class = new ClassType('Example');
$class
	->setExtends('\ParentClass')
	->addImplement('One')
	->addImplement('\Two')
	->addTrait('Three')
	->addTrait('\Four');

$class->addMethod('one')
	->setReturnType('One');

$method = $class->addMethod('two')
	->setReturnType('\Two');

$method->addParameter('one')
		->setTypeHint('One');

$method->addParameter('two')
		->setTypeHint('\Two');

Assert::matchFile(__DIR__ . '/PhpNamespace.fqn1.expect', (string) $class);


// global namespace
$class = new ClassType('Example', new PhpNamespace);
$class
	->setExtends('\ParentClass')
	->addImplement('One')
	->addImplement('\Two')
	->addTrait('Three')
	->addTrait('\Four');

$class->addMethod('one')
	->setReturnType('One');

$method = $class->addMethod('two')
	->setReturnType('\Two');

$method->addParameter('one')
		->setTypeHint('One');

$method->addParameter('two')
		->setTypeHint('\Two');

Assert::matchFile(__DIR__ . '/PhpNamespace.fqn2.expect', (string) $class);
