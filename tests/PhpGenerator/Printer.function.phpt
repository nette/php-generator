<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


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


// single
$function = new GlobalFunction('func');
$function->addAttribute('Foo', [1, 2, 3]);

Assert::match(<<<'XX'
	#[Foo(1, 2, 3)]
	function func()
	{
	}
	XX, $printer->printFunction($function));


// multiple
$function = new GlobalFunction('func');
$function->addAttribute('Foo', [1, 2, 3]);
$function->addAttribute('Bar');

Assert::match(<<<'XX'
	#[Foo(1, 2, 3)]
	#[Bar]
	function func()
	{
	}
	XX, $printer->printFunction($function));


// multiline
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


// parameter: single
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


// parameter: multiple
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


// parameter: multiline
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
