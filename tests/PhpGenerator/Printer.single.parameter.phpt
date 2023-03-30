<?php

declare(strict_types=1);

use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


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
