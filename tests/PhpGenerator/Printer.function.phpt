<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Printer;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


test('printFunction with typed parameter and return type', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$function
		->setReturnType('stdClass')
		->setBody("func(); \r\nreturn 123;")
		->addParameter('var')
			->setType('stdClass');

	Assert::match(<<<'XX'
		function func(stdClass $var): stdClass
		{
			func();
			return 123;
		}

		XX, $printer->printFunction($function));
});


test('printFunction with parameter attribute', function () {
	$printer = new Printer;
	$function = new GlobalFunction('multi');
	$function->addParameter('foo')
			->addAttribute('Foo');

	Assert::match(<<<'XX'
		function multi(
			#[Foo]
			$foo,
		) {
		}
		XX, $printer->printFunction($function));
});


test('printFunction with parameter attribute and return type', function () {
	$printer = new Printer;
	$function = new GlobalFunction('multiType');
	$function
		->setReturnType('array')
		->addParameter('foo')
			->addAttribute('Foo');

	Assert::match(<<<'XX'
		function multiType(
			#[Foo]
			$foo,
		): array
		{
		}
		XX, $printer->printFunction($function));
});


test('printFunction with single attribute on function', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$function->addAttribute('Foo', [1, 2, 3]);

	Assert::match(<<<'XX'
		#[Foo(1, 2, 3)]
		function func()
		{
		}
		XX, $printer->printFunction($function));
});


test('printFunction with multiple attributes on function', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$function->addAttribute('Foo', [1, 2, 3]);
	$function->addAttribute('Bar');

	same(
		<<<'XX'
			#[Foo(1, 2, 3)]
			#[Bar]
			function func()
			{
			}

			XX,
		(string) $function,
	);

	Assert::match(<<<'XX'
		#[Foo(1, 2, 3)]
		#[Bar]
		function func()
		{
		}
		XX, $printer->printFunction($function));
});


test('printFunction with multiline attribute on function', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$function->addAttribute('Foo', ['a', str_repeat('x', 120)]);

	Assert::match(<<<'XX'
		#[Foo(
			'a',
			'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
		)]
		function func()
		{
		}
		XX, $printer->printFunction($function));
});


test('printFunction with single attribute on parameter', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$param = $function->addParameter('foo');
	$param->addAttribute('Foo', [1, 2, 3]);

	Assert::match(<<<'XX'
		function func(
			#[Foo(1, 2, 3)]
			$foo,
		) {
		}
		XX, $printer->printFunction($function));
});


test('printFunction with multiple attributes on parameter', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$param = $function->addParameter('foo');
	$param->addAttribute('Foo', [1, 2, 3]);
	$param->addAttribute('Bar');

	Assert::match(<<<'XX'
		function func(
			#[Foo(1, 2, 3), Bar]
			$foo,
		) {
		}
		XX, $printer->printFunction($function));
});


test('printFunction with multiline attributes on multiple parameters', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$param = $function->addParameter('bar');
	$param->addAttribute('Foo');
	$param = $function->addParameter('foo');
	$param->addAttribute('Foo', ['a', str_repeat('x', 120)]);

	Assert::match(<<<'XX'
		function func(
			#[Foo]
			$bar,
			#[Foo(
				'a',
				'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
			)]
			$foo,
		) {
		}
		XX, $printer->printFunction($function));
});


test('printFunction with multiple and multiline attributes on parameter', function () {
	$printer = new Printer;
	$function = new GlobalFunction('func');
	$param = $function->addParameter('foo');
	$param->addAttribute('Bar');
	$param->addAttribute('Foo', ['a', str_repeat('x', 120)]);

	Assert::match(<<<'XX'
		function func(
			#[Bar]
			#[Foo(
				'a',
				'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
			)]
			$foo,
		) {
		}
		XX, $printer->printFunction($function));
});
