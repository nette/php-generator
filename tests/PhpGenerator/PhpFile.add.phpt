<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	(new PhpFile)->add((new PhpNamespace('Foo'))->add(new ClassType));
}, Nette\InvalidArgumentException::class, 'Class does not have a name.');


$phpFile = (new PhpFile)->add(($namespace = new PhpNamespace('FooBar'))
	->add(($classA = new ClassType('Foo'))->addMethod('baz')->setReturnType('FooBar\\Bar')->setBody('return new Bar();') ? $classA : $classA)
	->add(new ClassType('Bar'))
	);


same('<?php

namespace FooBar;

class Foo
{
	public function baz(): Bar
	{
		return new Bar();
	}
}

class Bar
{
}
', (string) $phpFile);

Assert::null($classA->getNamespace());
Assert::null($classA->getNamespace());
