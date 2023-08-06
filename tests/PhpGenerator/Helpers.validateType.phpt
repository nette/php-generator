<?php

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$foo = false;
Assert::null(Helpers::validateType('', $foo));
Assert::null(Helpers::validateType(null, $foo));
Assert::same('Foo', Helpers::validateType('Foo', $foo));
Assert::same('Foo\Bar', Helpers::validateType('Foo\Bar', $foo));
Assert::same('\Foo\Bar', Helpers::validateType('\Foo\Bar', $foo));
Assert::same('Foo', Helpers::validateType('?Foo', $foo));
Assert::true($foo);
Assert::same('Foo|Bar', Helpers::validateType('Foo|Bar', $foo));
Assert::same('Foo&Bar\X', Helpers::validateType('Foo&Bar\X', $foo));
Assert::same('(Foo&Bar\X)|Baz', Helpers::validateType('(Foo&Bar\X)|Baz', $foo));
Assert::same('Abc\C|(Abc\X&Abc\D)|null', Helpers::validateType('Abc\C|(Abc\X&Abc\D)|null', $foo));

Assert::exception(
	fn() => Helpers::validateType('-', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => Helpers::validateType('?Foo|Bar', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => Helpers::validateType('(Foo)', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => Helpers::validateType('(Foo&Bar)', $foo),
	Nette\InvalidArgumentException::class,
);
