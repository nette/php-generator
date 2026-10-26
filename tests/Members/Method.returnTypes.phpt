<?php declare(strict_types=1);

/**
 * Test: Nette\PhpGenerator - PHP7 return type declarations
 */

namespace A
{
	class Foo
	{
	}
}

namespace {
	use Nette\PhpGenerator\Method;
	use Tester\Assert;

	require __DIR__ . '/../bootstrap.php';

	// test from

	interface A
	{
		function testClass(): A\Foo;

		function testScalar(): string;
	}

	$method = Method::from([A::class, 'testClass']);
	Assert::same(A\Foo::class, (string) $method->getReturnType());

	$method = Method::from([A::class, 'testScalar']);
	Assert::same('string', (string) $method->getReturnType());

	// generating methods with return type declarations

	$method = (new Method('create'))
		->setReturnType('Foo')
		->setBody('return new Foo();');

	same(
		<<<'XX'
			function create(): Foo
			{
				return new Foo();
			}

			XX,
		(string) $method,
	);

}
