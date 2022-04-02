<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


$function = new GlobalFunction('test');
$function->setBody('return $a + $b;');
$function->addAttribute('ExampleAttribute');
$function->addComment('My Function');

same(
	<<<'XX'
		/**
		 * My Function
		 */
		#[ExampleAttribute]
		function test()
		{
			return $a + $b;
		}

		XX,
	(string) $function,
);
