<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


$printer = new Printer;
$namespace = new PhpNamespace('Foo');
$namespace->addUse('Example\Foo\EmailAlias\Bar');
$namespace->addUse('Example\Foo\Email\Test');
$namespace->addUse('Example\Foo\MyClass');

Assert::match(
	<<<'XX'
		namespace Foo;

		use Example\Foo\Email\Test;
		use Example\Foo\EmailAlias\Bar;
		use Example\Foo\MyClass;

		XX,
	$printer->printNamespace($namespace),
);
