<?php

/**
 * Test: Nette\PhpGenerator for classes.
 */

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');
$class
	->setAbstract(TRUE)
	->setFinal(TRUE)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('IOne')
	->addTrait('ObjectTrait')
	->addComment("Description of class.\nThis is example\n")
	->addComment('@property-read Nette\Forms\Form $form');

$class
	->addConst('ROLE', 'admin')
	->addConst('FORCE_ARRAY', new PhpLiteral('Nette\Utils\Json::FORCE_ARRAY'));

$class->addProperty('handle')
	->setVisibility('private')
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new PhpLiteral('RecursiveIteratorIterator::SELF_FIRST'));

$p = $class->addProperty('sections', ['first' => TRUE])
	->setStatic(TRUE);

Assert::same($p, $class->getProperty('sections'));

$m = $class->addMethod('getHandle')
	->addComment('Returns file handle.')
	->addComment('@return resource')
	->setFinal(TRUE)
	->setBody('return $this->?;', ['handle']);

Assert::same($m, $class->getMethod('getHandle'));

$class->addMethod('getSections')
	->setStatic(TRUE)
	->setVisibility('protected')
	->setReturnReference(TRUE)
	->addBody('$mode = ?;', [123])
	->addBody('return self::$sections;')
	->addParameter('mode', new PhpLiteral('self::ORDER'));

$method = $class->addMethod('show')
	->setAbstract(TRUE);

$method->addParameter('item');

$method->addParameter('res', NULL)
		->setReference(TRUE)
		->setTypeHint('array');

Assert::matchFile(__DIR__ . '/ClassType.expect', (string) $class);


// global setters & getters
$methods = $class->getMethods();
Assert::count(3, $methods);
$class->setMethods(array_values($methods));
Assert::same($methods, $class->getMethods());

$properties = $class->getProperties();
Assert::count(3, $properties);
$class->setProperties(array_values($properties));
Assert::same($properties, $class->getProperties());

$parameters = $method->getParameters();
Assert::count(2, $parameters);
$method->setParameters(array_values($parameters));
Assert::same($parameters, $method->getParameters());
