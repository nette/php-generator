<?php

/**
 * Test: Nette\PhpGenerator for classes.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Type;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');

Assert::false($class->isFinal());
Assert::false($class->isAbstract());
Assert::true($class->isClass());
Assert::false($class->isInterface());
Assert::false($class->isTrait());
Assert::same([], $class->getExtends());
Assert::same([], $class->getTraits());

$class
	->setAbstract(true)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('IOne')
	->addComment("Description of class.\nThis is example\n /**/")
	->addComment('@property-read Nette\Forms\Form $form');

$trait1 = $class->addTrait('ObjectTrait');
$trait2 = $class->addTrait('AnotherTrait')
	->addResolution('sayHello as protected');

$class->addConstant('ROLE', 'admin');
$class->addConstant('ACTIVE', false)
	->setFinal();

Assert::false($class->isFinal());
Assert::true($class->isAbstract());
Assert::same('ParentClass', $class->getExtends());
Assert::same(['ObjectTrait' => $trait1, 'AnotherTrait' => $trait2], $class->getTraits());
Assert::count(2, $class->getConstants());
Assert::type(Nette\PhpGenerator\Constant::class, $class->getConstants()['ROLE']);

$class->addConstant('FORCE_ARRAY', new Literal('Nette\Utils\Json::FORCE_ARRAY'))
	->setVisibility('private')
	->addComment('Commented');

$class->addProperty('handle')
	->setPrivate()
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new Literal('RecursiveIteratorIterator::SELF_FIRST'));

$class->addProperty('typed1')
	->setType(Type::ARRAY)
	->setReadOnly();

$class->addProperty('typed2')
	->setType(Type::ARRAY)
	->setNullable()
	->setInitialized();

$class->addProperty('typed3')
	->setType(Type::ARRAY)
	->setValue(null);

$p = $class->addProperty('sections', ['first' => true])
	->setStatic(true);

Assert::same($p, $class->getProperty('sections'));
Assert::true($class->hasProperty('sections'));
Assert::false($class->hasProperty('unknown'));
Assert::true($p->isStatic());
Assert::null($p->getVisibility());
Assert::false($p->isPrivate());
Assert::false($p->isProtected());
Assert::true($p->isPublic());

$m = $class->addMethod('getHandle')
	->addComment('Returns file handle.')
	->addComment('@return resource')
	->setFinal(true)
	->setBody('return $this->?;', ['handle']);

Assert::same($m, $class->getMethod('getHandle'));
Assert::true($class->hasMethod('getHandle'));
Assert::false($class->hasMethod('unknown'));
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
$m->addParameter('mode', new Literal('self::ORDER'));

Assert::false($m->isFinal());
Assert::true($m->isStatic());
Assert::true($m->getReturnReference());
Assert::false($m->isReturnNullable());
Assert::null($m->getReturnType());
Assert::same('protected', $m->getVisibility());
Assert::false($m->isPrivate());
Assert::true($m->isProtected());
Assert::false($m->isPublic());

$method = $class->addMethod('show')
	->setAbstract(true);

$method->addParameter('foo');
$method->removeParameter('foo');

$method->addParameter('item');

$method->addParameter('res', null)
		->setReference(true)
		->setType(Type::union(Type::ARRAY, 'null'));

$method->addParameter('bar', null)
		->setType('stdClass|string')
		->setNullable(true);

$class->addTrait('foo');
$class->removeTrait('foo');

$class->addImplement('foo');
$class->removeImplement('foo');

$class
	->addTrait('ThirdTrait')
		->addResolution('a as private foo')
		->addResolution('b as private bar')
		->addComment('@use Foo');


sameFile(__DIR__ . '/expected/ClassType.expect', (string) $class);


// global setters & getters
$methods = $class->getMethods();
Assert::count(3, $methods);
$class->setMethods(array_values($methods));
Assert::same($methods, $class->getMethods());

$properties = $class->getProperties();
Assert::count(6, $properties);
$class->setProperties(array_values($properties));
Assert::same($properties, $class->getProperties());

$parameters = $method->getParameters();
Assert::count(3, $parameters);
$method->setParameters(array_values($parameters));
Assert::same($parameters, $method->getParameters());


Assert::exception(function () {
	$class = new ClassType;
	$class->addMethod('method')->setVisibility('unknown');
}, Nette\InvalidArgumentException::class, 'Argument must be public|protected|private.');


// duplicity
$class = new ClassType('Example');
$class->addConstant('a', 1);
Assert::exception(function () use ($class) {
	$class->addConstant('a', 1);
}, Nette\InvalidStateException::class, "Cannot add constant 'a', because it already exists.");

$class->addProperty('a');
Assert::exception(function () use ($class) {
	$class->addProperty('a');
}, Nette\InvalidStateException::class, "Cannot add property 'a', because it already exists.");

$class->addMethod('a');
Assert::exception(function () use ($class) {
	$class->addMethod('a');
}, Nette\InvalidStateException::class, "Cannot add method 'a', because it already exists.");

Assert::exception(function () use ($class) {
	$class->addMethod('A');
}, Nette\InvalidStateException::class, "Cannot add method 'A', because it already exists.");

$class->addTrait('A');
Assert::exception(function () use ($class) {
	$class->addTrait('A');
}, Nette\InvalidStateException::class, "Cannot add trait 'A', because it already exists.");


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
$class->removeMethod('B')->removeMethod('c');

Assert::same(['a'], array_keys($class->getMethods()));
