<?php declare(strict_types=1);

/**
 * Test: Nette\PhpGenerator - PHP7 scalar type hints
 */

use Nette\PhpGenerator\{Method, Type};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

// test from


interface Foo
{
	function scalars(string $a, bool $b, int $c, float $d);
}

$method = Method::from([Foo::class, 'scalars']);
Assert::same('string', (string) $method->getParameters()['a']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('bool', (string) $method->getParameters()['b']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('int', (string) $method->getParameters()['c']->getType());

$method = Method::from([Foo::class, 'scalars']);
Assert::same('float', (string) $method->getParameters()['d']->getType());


// generating methods with scalar type hints

$method = (new Method('create'))
	->setBody('return null;');
$method->addParameter('a')->setType(Type::String);
$method->addParameter('b')->setType(Type::Bool);

same(
	<<<'XX'
		function create(string $a, bool $b)
		{
			return null;
		}

		XX,
	(string) $method,
);
