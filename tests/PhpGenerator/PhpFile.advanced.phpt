<?php declare(strict_types=1);

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('removeNamespace() removes namespace', function () {
	$file = new PhpFile;
	$file->addNamespace('Foo')->addClass('A');
	$file->addNamespace('Bar')->addClass('B');

	Assert::count(2, $file->getNamespaces());
	Assert::count(2, $file->getClasses());

	$file->removeNamespace('Foo');

	Assert::count(1, $file->getNamespaces());
	Assert::same(['Bar'], array_keys($file->getNamespaces()));
	Assert::same(['Bar\B'], array_keys($file->getClasses()));
});


test('addClass() with fully qualified name creates namespace', function () {
	$file = new PhpFile;

	$class = $file->addClass('Foo\Bar\Baz');

	Assert::same('Baz', $class->getName());
	Assert::same('Foo\Bar', $class->getNamespace()->getName());
	Assert::same(['Foo\Bar'], array_keys($file->getNamespaces()));
});


test('addClass() to global namespace', function () {
	$file = new PhpFile;

	$class = $file->addClass('GlobalClass');

	Assert::same('GlobalClass', $class->getName());
	Assert::same('', $class->getNamespace()->getName());
	Assert::same(['GlobalClass'], array_keys($file->getClasses()));
});


test('addInterface() with fully qualified name', function () {
	$file = new PhpFile;

	$interface = $file->addInterface('Foo\Bar\MyInterface');

	Assert::same('MyInterface', $interface->getName());
	Assert::same('Foo\Bar', $interface->getNamespace()->getName());
});


test('addTrait() with fully qualified name', function () {
	$file = new PhpFile;

	$trait = $file->addTrait('Foo\Bar\MyTrait');

	Assert::same('MyTrait', $trait->getName());
	Assert::same('Foo\Bar', $trait->getNamespace()->getName());
});


test('addEnum() with fully qualified name', function () {
	$file = new PhpFile;

	$enum = $file->addEnum('Foo\Bar\MyEnum');

	Assert::same('MyEnum', $enum->getName());
	Assert::same('Foo\Bar', $enum->getNamespace()->getName());
});


test('addFunction() with fully qualified name', function () {
	$file = new PhpFile;

	$function = $file->addFunction('Foo\Bar\myFunction');

	Assert::same('myFunction', $function->getName());
	Assert::same(['Foo\Bar\myFunction'], array_keys($file->getFunctions()));

	// Verify the function is in the correct namespace
	$namespace = $file->getNamespaces()['Foo\Bar'];
	Assert::same(['myFunction'], array_keys($namespace->getFunctions()));
});


test('addFunction() to global namespace', function () {
	$file = new PhpFile;

	$function = $file->addFunction('globalFunc');

	Assert::same('globalFunc', $function->getName());
	Assert::same(['globalFunc'], array_keys($file->getFunctions()));

	// Verify the function is in the global namespace
	$namespace = $file->getNamespaces()[''];
	Assert::same(['globalFunc'], array_keys($namespace->getFunctions()));
});


test('getClasses() aggregates across all namespaces', function () {
	$file = new PhpFile;

	$file->addNamespace('Foo')->addClass('A');
	$file->addNamespace('Foo')->addInterface('B');
	$file->addNamespace('Bar')->addClass('C');
	$file->addNamespace('Bar')->addTrait('D');
	$file->addNamespace('')->addClass('GlobalClass');

	$classes = $file->getClasses();

	Assert::same([
		'Foo\A',
		'Foo\B',
		'Bar\C',
		'Bar\D',
		'GlobalClass',
	], array_keys($classes));
});


test('getFunctions() aggregates across all namespaces', function () {
	$file = new PhpFile;

	$file->addFunction('Foo\funcA');
	$file->addFunction('Bar\funcB');
	$file->addFunction('globalFunc');

	$functions = $file->getFunctions();

	Assert::same([
		'Foo\funcA',
		'Bar\funcB',
		'globalFunc',
	], array_keys($functions));
});


test('addNamespace() returns existing namespace if already exists', function () {
	$file = new PhpFile;

	$ns1 = $file->addNamespace('Foo');
	$ns1->addClass('A');

	$ns2 = $file->addNamespace('Foo');
	$ns2->addClass('B');

	Assert::same($ns1, $ns2);
	Assert::count(2, $ns1->getClasses());
	Assert::same(['A', 'B'], array_keys($ns1->getClasses()));
});


test('addUse() adds to global namespace', function () {
	$file = new PhpFile;

	$file->addUse('Foo\Bar');
	$file->addUse('Baz\Qux', 'MyAlias');

	$globalNamespace = $file->getNamespaces()[''];
	$uses = $globalNamespace->getUses();

	Assert::count(2, $uses);
	Assert::same('Foo\Bar', $uses['Bar']);
	Assert::same('Baz\Qux', $uses['MyAlias']);
});


test('addUse() with different types', function () {
	$file = new PhpFile;

	$file->addUse('Foo\Bar', null, PhpNamespace::NameNormal);
	$file->addUse('Foo\myFunc', null, PhpNamespace::NameFunction);
	$file->addUse('Foo\MY_CONST', null, PhpNamespace::NameConstant);

	$globalNamespace = $file->getNamespaces()[''];

	Assert::count(1, $globalNamespace->getUses(PhpNamespace::NameNormal));
	Assert::count(1, $globalNamespace->getUses(PhpNamespace::NameFunction));
	Assert::count(1, $globalNamespace->getUses(PhpNamespace::NameConstant));
});



test('multiple add*() operations create classes in correct namespaces', function () {
	$file = new PhpFile;

	$file->addClass('App\Models\User');
	$file->addInterface('App\Contracts\Repository');
	$file->addTrait('App\Traits\Timestampable');
	$file->addEnum('App\Enums\Status');

	$namespaces = $file->getNamespaces();

	Assert::count(4, $namespaces); // App\Models, App\Contracts, App\Traits, App\Enums
	Assert::true(isset($namespaces['App\Models']));
	Assert::true(isset($namespaces['App\Contracts']));
	Assert::true(isset($namespaces['App\Traits']));
	Assert::true(isset($namespaces['App\Enums']));
});
