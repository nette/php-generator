<?php

declare(strict_types=1);

use Nette\PhpGenerator\Method;
require __DIR__ . '/../bootstrap.php';


$method = (new Method('create'))
	->setBody('return null;');

for ($name = 'a'; $name < 'm'; $name++) {
	$method->addParameter($name)->setType('string');
}

same(
	<<<'XX'
		function create(
			string $a,
			string $b,
			string $c,
			string $d,
			string $e,
			string $f,
			string $g,
			string $h,
			string $i,
			string $j,
			string $k,
			string $l,
		) {
			return null;
		}

		XX,
	(string) $method,
);
