<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$classy = (new ClassType('Classy'))
  ->addAttribute('MyAttribute', [0]);

same(
	<<<'XX'
		#[MyAttribute(0)]
		class Classy
		{
		}

		XX,
	(string) $classy,
);
