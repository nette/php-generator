<?php

/**
 * Test: Nette\PhpGenerator for classes.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');

Assert::false($class->isFinal());
Assert::false($class->isAbstract());
Assert::same([], $class->getExtends());
Assert::same([], $class->getTraits());

$class
	->setAbstract(TRUE)
	->setFinal(TRUE)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('IOne')
	->setTraits(['ObjectTrait'])
	->addTrait('AnotherTrait', ['sayHello as protected'])
	->addComment("Description of class.\nThis is example\n")
	->addComment('@property-read Nette\Forms\Form $form')
	->setConstants(['ROLE' => 'admin'])
	->addConstant('ACTIVE', FALSE);

Assert::true($class->isFinal());
Assert::true($class->isAbstract());
Assert::same('ParentClass', $class->getExtends());
Assert::same(['ObjectTrait', 'AnotherTrait'], $class->getTraits());
Assert::count(2, $class->getConstants());
Assert::type(Nette\PhpGenerator\Constant::class, $class->getConstants()['ROLE']);

$class->addConstant('FORCE_ARRAY', new PhpLiteral('Nette\Utils\Json::FORCE_ARRAY'))
	->setVisibility('private')
	->addComment('Commented');

$class->addProperty('handle')
	->setVisibility('private')
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new PhpLiteral('RecursiveIteratorIterator::SELF_FIRST'));

$p = $class->addProperty('sections', ['first' => TRUE])
	->setStatic(TRUE);

Assert::same($p, $class->getProperty('sections'));
Assert::true($p->isStatic());
Assert::null($p->getVisibility());

$m = $class->addMethod('getHandle')
	->addComment('Returns file handle.')
	->addComment('@return resource')
	->setFinal(TRUE)
	->setBody('return $this->?;', ['handle']);

Assert::same($m, $class->getMethod('getHandle'));
Assert::true($m->isFinal());
Assert::false($m->isStatic());
Assert::false($m->isAbstract());
Assert::false($m->getReturnReference());
Assert::same('public', $m->getVisibility());
Assert::same('return $this->handle;', $m->getBody());

$m = $class->addMethod('getSections')
	->setStatic(TRUE)
	->setVisibility('protected')
	->setReturnReference(TRUE)
	->addBody('$mode = ?;', [123])
	->addBody('return self::$sections;');
$m->addParameter('mode', new PhpLiteral('self::ORDER'));

Assert::false($m->isFinal());
Assert::true($m->isStatic());
Assert::true($m->getReturnReference());
Assert::false($m->getReturnNullable());
Assert::null($m->getReturnType());
Assert::same('protected', $m->getVisibility());

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


Assert::exception(function () {
	$class = new ClassType;
	$class->addMethod('method')->setVisibility('unknown');
}, Nette\InvalidArgumentException::class, 'Argument must be public|protected|private.');
