<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;


require __DIR__ . '/../bootstrap.php';


/** global */
function func(stdClass $a, $b = null)
{
}


$function = GlobalFunction::from('func');
same(
'/**
 * global
 */
function func(stdClass $a, $b = null)
{
}
', (string) $function);
