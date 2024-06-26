<?php

declare(strict_types=1);

namespace Abc;

use Nette;
use function substr;
use const BAR;

abstract class Class7
{
	abstract function abstractFun();

	function emptyFun() {}

	function emptyFun2() {
	}

	function simple(){return 1;}

	function simple2()
	{
		return 1;
	}

	function long()
	{
		// comment
		if ($member instanceof Method) {
			$s = [1, 2, 3];
		}
		/*
		$this->methods[$member->getName()] = $member;
		*/
		throw new Nette\InvalidArgumentException('Argument must be Method|Property|Constant.');
	}

	function resolving($a = a\FOO, ?self $b = null, $c = self::FOO)
	{
		// constants
		echo FOO;
		echo \FOO;
		echo a\FOO;
		echo \Nette\FOO;

		// functions
		func();
		\func();
		a\func();
		\Nette\func();

		// classes
		$x = new MyClass;
		$y = new \stdClass;
		$z = Nette\Utils\ArrayHash::class;
	}

	function complex()
	{
		echo 1;
		// single line comment

    // spaces - indent
        // spaces - indent

		/* multi
		line
		comment */
		if (
			$a
			&&		$b    + $c)
		{}

		/** multi
		line
		comment */
		// Alias Method will not be resolved in comment
		if ($member instanceof Method) {
		// inline HTML is not supported
			?>
a
	b
		c
			<?php
		}
		throw new Nette\InvalidArgumentException();
	}
}
