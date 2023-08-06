<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
require __DIR__ . '/../bootstrap.php';

$namespace = new PhpNamespace('Foo');
$namespace->addClass('Bar');

$phpFile = new PhpFile;
$phpFile->addNamespace('Foo');
$phpFile->addNamespace($namespace); // overwrite


same(
	<<<'XX'
		<?php

		namespace Foo;

		class Bar
		{
		}

		XX,
	(string) $phpFile,
);
