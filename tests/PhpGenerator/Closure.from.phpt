<?php

declare(strict_types=1);

use Nette\PhpGenerator\Closure;


require __DIR__ . '/../bootstrap.php';


$closure = #[ExampleAttribute] function (stdClass $a, $b = null) {};

$function = Closure::from($closure);
same(
	<<<'XX'
		#[ExampleAttribute] function (stdClass $a, $b = null) {
		}
		XX,
	(string) $function,
);
