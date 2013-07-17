<?php

/**
 * Test: Nette\PhpGenerator for classes.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\PhpGenerator\ClassType,
	Nette\PhpGenerator\PhpLiteral;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');
$class
	->setAbstract(TRUE)
	->setFinal(TRUE)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('IOne')
	->addTrait('ObjectTrait')
	->addDocument("Description of class.\nThis is example\n")
	->addDocument('@property-read Nette\Forms\Form $form');

$class
	->addConst('ROLE', 'admin')
	->addConst('FORCE_ARRAY', new PhpLiteral('Nette\Utils\Json::FORCE_ARRAY'));

$class->addProperty('handle')
	->setVisibility('private')
	->addDocument('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new PhpLiteral('RecursiveIteratorIterator::SELF_FIRST'));

$class->addProperty('sections', array('first' => TRUE))
	->setStatic(TRUE);

$class->addMethod('getHandle')
	->addDocument('Returns file handle.')
	->addDocument('@return resource')
	->setFinal(TRUE)
	->setBody('return $this->?;', array('handle'));

$class->addMethod('getSections')
	->setStatic(TRUE)
	->setVisibility('protected')
	->setReturnReference(TRUE)
	->addBody('$mode = ?;', array(123))
	->addBody('return self::$sections;')
	->addParameter('mode', new PhpLiteral('self::ORDER'));

$method = $class->addMethod('show')
	->setAbstract(TRUE);

$method->addParameter('item');

$method->addParameter('res', NULL)
		->setReference(TRUE)
		->setTypeHint('array');

Assert::matchFile(__DIR__ . '/ClassType.expect', (string) $class);
