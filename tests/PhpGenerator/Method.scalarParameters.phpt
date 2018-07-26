<?php

/**
 * Test: Nette\PhpGenerator - PHP7 scalar type hints
 */

declare(strict_types=1);


use Nette\PhpGenerator\Method;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

// test from


interface Foo
{
	function scalars(string $a, bool $b, int $c, float $d);
}

$method = Method::from(Foo::class . '::scalars');
Assert::same('string', $method->getParameters()['a']->getTypeHint());

$method = Method::from(Foo::class . '::scalars');
Assert::same('bool', $method->getParameters()['b']->getTypeHint());

$method = Method::from(Foo::class . '::scalars');
Assert::same('int', $method->getParameters()['c']->getTypeHint());

$method = Method::from(Foo::class . '::scalars');
Assert::same('float', $method->getParameters()['d']->getTypeHint());


// generating methods with scalar type hints

$method = (new Method('create'))
	->setBody('return null;');
$method->addParameter('a')->setTypeHint('string');
$method->addParameter('b')->setTypeHint('bool');
$method->addParameter('c')->setDefaultValueConstantName('DateTime::ISO8601')->setTypeHint('string');

Assert::match(
'function create(string $a, bool $b, string $c = DateTime::ISO8601)
{
	return null;
}
', (string) $method);
