<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


$function = new GlobalFunction('test');
$function->setBody('return $a + $b;');
$function->addAttribute('ExampleAttribute');
$function->addComment('My Function');

same(
	'/**
 * My Function
 */
#[ExampleAttribute]
function test()
{
	return $a + $b;
}
',
	(string) $function,
);


/** global */
function func(stdClass $a, $b = null)
{
	echo sprintf('hello, %s', 'world');
	return 1;
}


$function = GlobalFunction::from('func');
same(
	'/**
 * global
 */
function func(stdClass $a, $b = null)
{
}
',
	(string) $function,
);


$function = GlobalFunction::from('func', withBody: true);
same(<<<'XX'
	/**
	 * global
	 */
	function func(stdClass $a, $b = null)
	{
		echo \sprintf('hello, %s', 'world');
		return 1;
	}

	XX, (string) $function);
