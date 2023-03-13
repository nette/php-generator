<?php

declare(strict_types=1);

use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$printer = new Printer;
$function = new Nette\PhpGenerator\GlobalFunction('func');
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


$function = new Nette\PhpGenerator\GlobalFunction('multi');
$function->addParameter('foo')
		->addAttribute('Foo');

Assert::match(<<<'XX'
	function multi(
		#[Foo] $foo,
	) {
	}

	XX, $printer->printFunction($function));


$function = new Nette\PhpGenerator\GlobalFunction('multiType');
$function
	->setReturnType('array')
	->addParameter('foo')
		->addAttribute('Foo');

Assert::match(<<<'XX'
	function multiType(
		#[Foo] $foo,
	): array
	{
	}

	XX, $printer->printFunction($function));
