<?php

/**
 * Test: Nette\PhpGenerator & variadics.
 */

declare(strict_types=1);

use Nette\PhpGenerator\Method;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// test from

interface Variadics
{
	function foo(...$foo);

	function bar($foo, array &...$bar);
}

$method = Method::from([Variadics::class, 'foo']);
Assert::true($method->isVariadic());

$method = Method::from([Variadics::class, 'bar']);
Assert::true($method->isVariadic());
Assert::true($method->getParameters()['bar']->isReference());
Assert::same('array', $method->getParameters()['bar']->getType());



// test generating

// parameterless variadic method
$method = (new Method('variadic'))
	->setVariadic()
	->setBody('return 42;');

same(
	<<<'XX'
		function variadic()
		{
			return 42;
		}

		XX,
	(string) $method,
);


// variadic method with one parameter
$method = (new Method('variadic'))
	->setVariadic()
	->setBody('return 42;');
$method->addParameter('foo');

same(
	<<<'XX'
		function variadic(...$foo)
		{
			return 42;
		}

		XX,
	(string) $method,
);


// variadic method with multiple parameters
$method = (new Method('variadic'))
	->setVariadic()
	->setBody('return 42;');
$method->addParameter('foo');
$method->addParameter('bar');
$method->addParameter('baz', []);

same(
	<<<'XX'
		function variadic($foo, $bar, ...$baz)
		{
			return 42;
		}

		XX,
	(string) $method,
);


// method with typehinted variadic param
$method = (new Method('variadic'))
	->setVariadic()
	->setBody('return 42;');
$method->addParameter('foo')->setType('array');

same(
	<<<'XX'
		function variadic(array ...$foo)
		{
			return 42;
		}

		XX,
	(string) $method,
);


// method with typrhinted by-value variadic param
$method = (new Method('variadic'))
	->setVariadic()
	->setBody('return 42;');
$method->addParameter('foo')->setType('array')->setReference();

same(
	<<<'XX'
		function variadic(array &...$foo)
		{
			return 42;
		}

		XX,
	(string) $method,
);
