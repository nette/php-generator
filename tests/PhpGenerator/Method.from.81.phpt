<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\Method;

require __DIR__ . '/../bootstrap.php';


class Foo
{
	public static function bar(int $a, ...$b): void
	{
	}
}

$method = Method::from(Foo::bar(...));
same(
	<<<'XX'
		public static function bar(int $a, ...$b): void
		{
		}

		XX,
	(string) $method,
);
