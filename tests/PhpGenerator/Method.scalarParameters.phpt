<?php

/**
 * Test: Nette\PhpGenerator - PHP7 scalar type hints
 */

declare(strict_types=1);


use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Type;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

// test from


interface Foo
{
	function scalars(string $a, bool $b, int $c, float $d);
}

$method = Method::from([Foo::class, 'scalars']);
Assert::same('string', $method->getParameters()['a']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('bool', $method->getParameters()['b']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('int', $method->getParameters()['c']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('float', $method->getParameters()['d']->getType());


// generating methods with scalar type hints

$method = (new Method('create'))
	->setBody('return null;');
$method->addParameter('a')->setType(Type::String);
$method->addParameter('b')->setType(Type::Bool);

same(
	'function create(string $a, bool $b)
{
	return null;
}
',
	(string) $method,
);
