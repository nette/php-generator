<?php

declare(strict_types=1);

use Nette\PhpGenerator\Closure;


require __DIR__ . '/../bootstrap.php';


$function = new Closure;
$function->setBody('return null;');

for ($name = 'abcde'; $name < 'abcdr'; $name++) {
	$function->addParameter($name);
	$function->addUse($name);
}

same(
'function (
	$abcde,
	$abcdf,
	$abcdg,
	$abcdh,
	$abcdi,
	$abcdj,
	$abcdk,
	$abcdl,
	$abcdm,
	$abcdn,
	$abcdo,
	$abcdp,
	$abcdq
) use (
	$abcde,
	$abcdf,
	$abcdg,
	$abcdh,
	$abcdi,
	$abcdj,
	$abcdk,
	$abcdl,
	$abcdm,
	$abcdn,
	$abcdo,
	$abcdp,
	$abcdq
) {
	return null;
}', (string) $function);
