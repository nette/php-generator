<?php declare(strict_types=1);

/**
 * Test: nullability of a return type is part of the type
 */

use Nette\PhpGenerator\Method;
use Nette\Utils\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$method = (new Method('a'))->setReturnType('?int');
Assert::type(Type::class, $method->getReturnType());
Assert::same('?int', (string) $method->getReturnType());
Assert::true($method->isReturnNullable());

$method = (new Method('a'))->setReturnType('int|null');
Assert::same('?int', (string) $method->getReturnType());
Assert::true($method->isReturnNullable());

$method = (new Method('a'))->setReturnType('int')->setReturnNullable();
Assert::same('?int', (string) $method->getReturnType());
Assert::same('int', (string) $method->setReturnNullable(false)->getReturnType());
Assert::false($method->isReturnNullable());

$method = (new Method('a'))->setReturnType('A&B')->setReturnNullable();
Assert::same('(A&B)|null', (string) $method->getReturnType());


// no return type
$method = new Method('a');
Assert::null($method->getReturnType());
Assert::false($method->isReturnNullable());

Assert::exception(
	fn() => (new Method('a'))->setReturnNullable(),
	Nette\InvalidStateException::class,
	'Call setReturnType() before setReturnNullable().',
);


// Type object
$method = (new Method('a'))->setReturnType(Type::fromString('?Foo'));
Assert::same('?Foo', (string) $method->getReturnType());
Assert::true($method->isReturnNullable());
