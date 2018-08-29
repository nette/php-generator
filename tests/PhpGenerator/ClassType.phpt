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
Assert::same([], $class->getTraitResolutions());

$class
	->setAbstract(true)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('IOne')
	->setTraits(['ObjectTrait'])
	->addTrait('AnotherTrait', ['sayHello as protected'])
	->addComment("Description of class.\nThis is example\n")
	->addComment('@property-read Nette\Forms\Form $form')
	->setConstants(['ROLE' => 'admin'])
	->addConstant('ACTIVE', false);

Assert::false($class->isFinal());
Assert::true($class->isAbstract());
Assert::same('ParentClass', $class->getExtends());
Assert::same(['ObjectTrait', 'AnotherTrait'], $class->getTraits());
Assert::same(['ObjectTrait' => [], 'AnotherTrait' => ['sayHello as protected']], $class->getTraitResolutions());
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

$p = $class->addProperty('sections', ['first' => true])
	->setStatic(true);

Assert::same($p, $class->getProperty('sections'));
Assert::true($p->isStatic());
Assert::null($p->getVisibility());

$m = $class->addMethod('getHandle')
	->addComment('Returns file handle.')
	->addComment('@return resource')
	->setFinal(true)
	->setBody('return $this->?;', ['handle']);

Assert::same($m, $class->getMethod('getHandle'));
Assert::true($m->isFinal());
Assert::false($m->isStatic());
Assert::false($m->isAbstract());
Assert::false($m->getReturnReference());
Assert::same('public', $m->getVisibility());
Assert::same('return $this->handle;', $m->getBody());

$m = $class->addMethod('getSections')
	->setStatic(true)
	->setVisibility('protected')
	->setReturnReference(true)
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
	->setAbstract(true);

$method->addParameter('foo');
$method->removeParameter('foo');

$method->addParameter('item');

$method->addParameter('res', null)
		->setReference(true)
		->setTypeHint('array');

sameFile(__DIR__ . '/expected/ClassType.expect', (string) $class);


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


// remove members
$class = new ClassType('Example');
$class->addConstant('a', 1);
$class->addConstant('b', 1);
$class->removeConstant('b')->removeConstant('c');

Assert::same(['a'], array_keys($class->getConstants()));

$class->addProperty('a');
$class->addProperty('b');
$class->removeProperty('b')->removeProperty('c');

Assert::same(['a'], array_keys($class->getProperties()));

$class->addMethod('a');
$class->addMethod('b');
$class->removeMethod('b')->removeMethod('c');

Assert::same(['a'], array_keys($class->getMethods()));
