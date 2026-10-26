<?php declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$validate = fn(?string $type, bool &$nullable = false) => @Helpers::validateType($type, $nullable); // @ deprecated call tested on purpose

$foo = false;
Assert::null($validate('', $foo));
Assert::null($validate(null, $foo));
Assert::same('Foo', $validate('Foo', $foo));
Assert::same('Foo\Bar', $validate('Foo\Bar', $foo));
Assert::same('\Foo\Bar', $validate('\Foo\Bar', $foo));
Assert::same('Foo', $validate('?Foo', $foo));
Assert::true($foo);
Assert::same('Foo|Bar', $validate('Foo|Bar', $foo));
Assert::same('Foo&Bar\X', $validate('Foo&Bar\X', $foo));
Assert::same('(Foo&Bar\X)|Baz', $validate('(Foo&Bar\X)|Baz', $foo));
Assert::same('Abc\C|(Abc\X&Abc\D)|null', $validate('Abc\C|(Abc\X&Abc\D)|null', $foo));

Assert::exception(
	fn() => $validate('-', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => $validate('?Foo|Bar', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => $validate('(Foo)', $foo),
	Nette\InvalidArgumentException::class,
);

Assert::exception(
	fn() => $validate('(Foo&Bar)', $foo),
	Nette\InvalidArgumentException::class,
);
