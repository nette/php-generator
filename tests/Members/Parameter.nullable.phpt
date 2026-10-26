<?php declare(strict_types=1);

/**
 * Test: nullability of a parameter is part of its type
 */

use Nette\PhpGenerator\Parameter;
use Nette\Utils\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// both spellings of a nullable type mean the same
$param = (new Parameter('a'))->setType('?int');
Assert::type(Type::class, $param->getType());
Assert::same('?int', (string) $param->getType());
Assert::true($param->isNullable());

$param = (new Parameter('a'))->setType('int|null');
Assert::same('?int', (string) $param->getType());
Assert::true($param->isNullable());


// setNullable() after setType()
$param = (new Parameter('a'))->setType('int')->setNullable();
Assert::same('?int', (string) $param->getType());
Assert::true($param->isNullable());

$param->setNullable(false);
Assert::same('int', (string) $param->getType());
Assert::false($param->isNullable());


// setType() replaces the whole type including nullability
$param = (new Parameter('a'))->setType('Foo')->setNullable()->setType('Bar');
Assert::same('Bar', (string) $param->getType());

$param = (new Parameter('a'))->setType('?Foo')->setType('Bar');
Assert::same('Bar', (string) $param->getType());


// union and DNF types
$param = (new Parameter('a'))->setType('A|B')->setNullable();
Assert::same('A|B|null', (string) $param->getType());

$param = (new Parameter('a'))->setType('(A&B)|null');
Assert::true($param->isNullable());
Assert::same('A&B', (string) $param->setNullable(false)->getType());

$param = (new Parameter('a'))->setType('A&B')->setNullable();
Assert::same('(A&B)|null', (string) $param->getType());

$param = (new Parameter('a'))->setType('(A&B)|(C&D)|null')->setNullable(false);
Assert::same('(A&B)|(C&D)', (string) $param->getType());


// null cannot be removed from 'null' and 'mixed'
$param = (new Parameter('a'))->setType('mixed');
Assert::true($param->isNullable());
Assert::same('mixed', (string) $param->setNullable(false)->getType());

$param = (new Parameter('a'))->setType('null');
Assert::true($param->isNullable());
Assert::same('null', (string) $param->setNullable(false)->getType());


// a null default is folded into the type, in either order
$param = (new Parameter('a'))->setType('Foo')->setDefaultValue(null);
Assert::same('?Foo', (string) $param->getType());

$param = (new Parameter('a'))->setDefaultValue(null)->setType('Foo');
Assert::same('?Foo', (string) $param->getType());

$param->setNullable(false); // the null default keeps the type nullable
Assert::same('?Foo', (string) $param->getType());

$param->setDefaultValue(5); // the folded null stays
Assert::same('?Foo', (string) $param->getType());


// untyped parameter
$param = new Parameter('a');
Assert::null($param->getType());
Assert::false($param->isNullable());
Assert::true($param->setDefaultValue(null)->isNullable());

Assert::exception(
	fn() => (new Parameter('a'))->setNullable(),
	Nette\InvalidStateException::class,
	'Call setType() before setNullable().',
);


// Type object, empty and invalid input
$param = (new Parameter('a'))->setType(Type::fromString('int|string'));
Assert::same('int|string', (string) $param->getType());

Assert::null((new Parameter('a'))->setType('')->getType());
Assert::exception(
	fn() => (new Parameter('a'))->setType('?A|B'),
	Nette\InvalidArgumentException::class,
);
