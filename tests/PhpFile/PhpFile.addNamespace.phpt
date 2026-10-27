<?php declare(strict_types=1);

use Nette\PhpGenerator\{PhpFile, PhpNamespace};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$namespace = new PhpNamespace('Foo');
$namespace->addClass('Bar');

$phpFile = new PhpFile;
$phpFile->add($namespace);


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

// addNamespace() returns the existing namespace
Assert::same($namespace, $phpFile->addNamespace('Foo'));

// re-adding the same instance is fine
$phpFile->add($namespace);
Assert::same($namespace, $phpFile->getNamespaces()['Foo']);

Assert::exception(
	fn() => $phpFile->add(new PhpNamespace('Foo')),
	Nette\InvalidStateException::class,
	"Namespace 'Foo' already exists in the file.",
);

// add() refuses to replace even an empty namespace
$phpFile2 = new PhpFile;
$phpFile2->addNamespace('Bar');
Assert::exception(
	fn() => $phpFile2->add(new PhpNamespace('Bar')),
	Nette\InvalidStateException::class,
	"Namespace 'Bar' already exists in the file.",
);
