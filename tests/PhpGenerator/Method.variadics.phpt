<?php

/**
 * Test: Nette\PhpGenerator & variadics.
 * @phpversion 5.6
 */

use Nette\PhpGenerator\Method,
	Nette\PhpGenerator\Parameter,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


// test from

interface Variadics
{
	function foo(...$foo);
	function bar($foo, array &...$bar);
}

$method = Method::from(Variadics::class .'::foo');
Assert::true($method->isVariadic());

$method = Method::from(Variadics::class . '::bar');
Assert::true($method->isVariadic());
Assert::true($method->getParameters()['bar']->isReference());
Assert::same('array', $method->getParameters()['bar']->getTypeHint());



// test generating

// parameterless variadic method
$method = (new Method)
	->setName('variadic')
	->setVariadic(TRUE)
	->setBody('return 42;');

Assert::match(
'function variadic()
{
	return 42;
}
', (string) $method);


// variadic method with one parameter
$method = (new Method)
	->setName('variadic')
	->setVariadic(TRUE)
	->setBody('return 42;');
$method->addParameter('foo');

Assert::match(
'function variadic(...$foo)
{
	return 42;
}
', (string) $method);


// variadic method with multiple parameters
$method = (new Method)
	->setName('variadic')
	->setVariadic(TRUE)
	->setBody('return 42;');
$method->addParameter('foo');
$method->addParameter('bar');
$method->addParameter('baz', []);

Assert::match(
'function variadic($foo, $bar, ...$baz)
{
	return 42;
}
', (string) $method);


// method with typehinted variadic param
$method = (new Method)
	->setName('variadic')
	->setVariadic(TRUE)
	->setBody('return 42;');
$method->addParameter('foo')->setTypeHint('array');

Assert::match(
'function variadic(array ...$foo)
{
	return 42;
}
', (string) $method);


// method with typrhinted by-value variadic param
$method = (new Method)
	->setName('variadic')
	->setVariadic(TRUE)
	->setBody('return 42;');
$method->addParameter('foo')->setTypeHint('array')->setReference(TRUE);

Assert::match(
'function variadic(array &...$foo)
{
	return 42;
}
', (string) $method);
