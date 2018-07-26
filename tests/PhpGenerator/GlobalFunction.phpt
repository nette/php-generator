<?php
declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = null, string $c = DateTime::ISO8601)
{
}


$function = GlobalFunction::from('func');
Assert::match(
'/**
 * global
 */
function func(stdClass $a, $b = null, string $c = DateTime::ISO8601)
{
}', (string) $function);
