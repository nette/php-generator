<?php declare(strict_types=1);

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Printer;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


test('singleParameterOnOneLine formats function with one parameter on single line', function () {
	$printer = new Printer;
	$printer->singleParameterOnOneLine = true;

	$function = new Nette\PhpGenerator\GlobalFunction('singleFunction');
	$function
		->setReturnType('array')
		->addParameter('foo')
			->addAttribute('Foo');

	Assert::match(<<<'XX'
		function singleFunction(#[Foo] $foo): array
		{
		}

		XX, $printer->printFunction($function));
});


test('singleParameterOnOneLine formats method with one parameter on single line', function () {
	$printer = new Printer;
	$printer->singleParameterOnOneLine = true;

	$method = new Nette\PhpGenerator\Method('singleMethod');
	$method
		->setPublic()
		->setReturnType('array')
		->addParameter('foo')
			->addAttribute('Foo');

	Assert::match(<<<'XX'
		public function singleMethod(#[Foo] $foo): array
		{
		}

		XX, $printer->printMethod($method));
});


test('singleParameterOnOneLine formats promoted parameter on single line', function () {
	$printer = new Printer;
	$printer->singleParameterOnOneLine = true;

	$method = new Nette\PhpGenerator\Method('singleMethod');
	$method
		->setPublic()
		->setReturnType('array')
		->addPromotedParameter('foo')
			->setPublic();

	Assert::match(<<<'XX'
		public function singleMethod(public $foo): array
		{
		}

		XX, $printer->printMethod($method));
});


test('singleParameterOnOneLine wraps to multiple lines when parameter name is too long', function () {
	$printer = new Printer;
	$printer->singleParameterOnOneLine = true;

	$method = new Nette\PhpGenerator\Method('singleMethod');
	$method
		->setPublic()
		->setReturnType('array')
		->addPromotedParameter('looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong')
			->setPublic();

	Assert::match(<<<'XX'
		public function singleMethod(
			public $looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong,
		): array
		{
		}

		XX, $printer->printMethod($method));
});


test('singleParameterOnOneLine wraps to multiple lines when attribute contains newline', function () {
	$printer = new Printer;
	$printer->singleParameterOnOneLine = true;

	$method = new Nette\PhpGenerator\Method('singleMethod');
	$method->addParameter('foo')
		->addAttribute('Foo', [new Literal("'\n'")]);

	Assert::match(<<<'XX'
		function singleMethod(
			#[Foo('
			')]
			$foo,
		) {
		}
		XX, $printer->printMethod($method));
});
