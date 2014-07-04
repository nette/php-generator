<?php

use Nette\PhpGenerator\ClassType,
	ReflectionClass,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class A
{

	function foo()
	{
		echo 'foo';

		foreach (range(1, 2) as $no) {
			if (TRUE === FALSE) {
				echo $class->{'get' . \Nette\Utils\Random::generate(244, "foo{$rand}bar")}();
			}

			echo $no;
		}

		return function () use ($no) {
			return 3 * 5;
		};
	}

}


Assert::matchFile(__DIR__ . '/Method.body.expect', (string) ClassType::from('A'));
