<?php

/**
 * Test: PhpFile::add() method.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('add class without namespace', function () {
	$file = new PhpFile;
	$class = new ClassType('Demo');
	$file->add($class);

	Assert::same([''], array_keys($file->getNamespaces()));
	Assert::same(['Demo'], array_keys($file->getClasses()));
});


test('add class with namespace', function () {
	$file = new PhpFile;
	$namespace = new PhpNamespace('App\Model');
	$class = new ClassType('User', $namespace);
	$file->add($class);

	Assert::same(['App\Model'], array_keys($file->getNamespaces()));
	Assert::same(['App\Model\User'], array_keys($file->getClasses()));
});


test('add class to existing namespace', function () {
	$file = new PhpFile;
	$existingNamespace = $file->addNamespace('Foo\Bar');
	$existingNamespace->addClass('First');

	$namespace = new PhpNamespace('Foo\Bar');
	$class = new ClassType('Second', $namespace);
	$file->add($class);

	Assert::same(['Foo\Bar'], array_keys($file->getNamespaces()));
	Assert::same(['Foo\Bar\First', 'Foo\Bar\Second'], array_keys($file->getClasses()));
	Assert::same($existingNamespace, $file->addNamespace('Foo\Bar'));
});


test('add function without namespace', function () {
	$file = new PhpFile;
	$function = new GlobalFunction('myFunc');
	$function->setBody('return 42;');
	$file->add($function);

	Assert::same([''], array_keys($file->getNamespaces()));
	Assert::same(['myFunc'], array_keys($file->getFunctions()));
});


test('add class from reflection', function () {
	$file = new PhpFile;
	$stdClass = ClassType::from('stdClass');
	$file->add($stdClass);

	Assert::same($stdClass, $file->getClasses()['stdClass']);
});


test('multiple items to different namespaces', function () {
	$file = new PhpFile;

	$ns1 = new PhpNamespace('Foo');
	$class1 = new ClassType('A', $ns1);
	$file->add($class1);

	$ns2 = new PhpNamespace('Bar');
	$class2 = new ClassType('B', $ns2);
	$file->add($class2);

	Assert::same(['Foo', 'Bar'], array_keys($file->getNamespaces()));
	Assert::same(['Foo\A', 'Bar\B'], array_keys($file->getClasses()));
});


test('items are correctly added with strict types', function () {
	$file = new PhpFile;
	$file->setStrictTypes();

	$ns = new PhpNamespace('Test');
	$class = new ClassType('Sample', $ns);
	$class->addProperty('name')->setType('string');
	$file->add($class);

	Assert::same(['Test'], array_keys($file->getNamespaces()));
	Assert::same(['Test\Sample'], array_keys($file->getClasses()));
	Assert::true($file->hasStrictTypes());
});


test('add namespace directly', function () {
	$file = new PhpFile;
	$namespace = new PhpNamespace('App\Services');
	$namespace->addClass('MyService');
	$file->add($namespace);

	Assert::same(['App\Services'], array_keys($file->getNamespaces()));
	Assert::same(['App\Services\MyService'], array_keys($file->getClasses()));
});


test('add namespace with multiple items', function () {
	$file = new PhpFile;
	$namespace = new PhpNamespace('Foo\Bar');
	$namespace->addClass('First');
	$namespace->addClass('Second');
	$namespace->addFunction('helper');
	$file->add($namespace);

	Assert::same(['Foo\Bar'], array_keys($file->getNamespaces()));
	Assert::same(['Foo\Bar\First', 'Foo\Bar\Second'], array_keys($file->getClasses()));
	Assert::same(['Foo\Bar\helper'], array_keys($file->getFunctions()));
});


testException('adding namespace object to existing namespace throws exception', function () {
	$file = new PhpFile;
	$existingNs = $file->addNamespace('App');
	$existingNs->addClass('Existing');

	$duplicateNs = new PhpNamespace('App');
	$duplicateNs->addClass('Duplicate');

	$file->add($duplicateNs);
}, Nette\InvalidStateException::class, "Namespace 'App' already exists in the file.");
