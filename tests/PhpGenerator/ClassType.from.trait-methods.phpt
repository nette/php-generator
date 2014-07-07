<?php

/**
 * Test: Nette\PhpGenerator generator.
 * @phpversion 5.4
 */

namespace
{

	require __DIR__ . '/../bootstrap.php';

}


namespace MyTraits
{

	trait TTrait
	{

		function foo()
		{
			return 3;
		}

	}

}


namespace MyClasses
{

	use MyTraits as MTr;


	class A
	{

		use MTr\TTrait;


		function bar()
		{
			return 4;
		}

	}


	\Tester\Assert::matchFile(__DIR__ . '/ClassType.from.trait-methods.expect', (string) \Nette\PhpGenerator\ClassType::from('\MyClasses\A'));

}
