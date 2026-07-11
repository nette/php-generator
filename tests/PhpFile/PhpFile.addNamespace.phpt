<?php declare(strict_types=1);

use Nette\PhpGenerator\{PhpFile, PhpNamespace};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$namespace = new PhpNamespace('Foo');
$namespace->addClass('Bar');

$phpFile = new PhpFile;
$phpFile->addNamespace('Foo');
@$phpFile->addNamespace($namespace); // @ deprecated call tested on purpose; overwrites the empty namespace


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

// re-adding the same instance is fine
Assert::same($namespace, @$phpFile->addNamespace($namespace)); // @ deprecated call tested on purpose

// a non-empty namespace cannot be silently replaced
Assert::exception(
	fn() => @$phpFile->addNamespace(new PhpNamespace('Foo')), // @ deprecated call tested on purpose
	Nette\InvalidStateException::class,
	"Namespace 'Foo' already exists in the file.",
);

Assert::exception(
	fn() => $phpFile->add(new PhpNamespace('Foo')),
	Nette\InvalidStateException::class,
	"Namespace 'Foo' already exists in the file.",
);

// unlike addNamespace(), add() refuses to replace even an empty namespace
$phpFile2 = new PhpFile;
$phpFile2->addNamespace('Bar');
Assert::exception(
	fn() => $phpFile2->add(new PhpNamespace('Bar')),
	Nette\InvalidStateException::class,
	"Namespace 'Bar' already exists in the file.",
);
