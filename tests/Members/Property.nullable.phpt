<?php declare(strict_types=1);

/**
 * Test: nullability of a property is part of its type
 */

use Nette\PhpGenerator\Property;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$prop = (new Property('a'))->setType('int|null');
Assert::same('?int', (string) $prop->getType());
Assert::true($prop->isNullable());

$prop = (new Property('a'))->setType('int')->setNullable();
Assert::same('?int', (string) $prop->getType());
Assert::same('int', (string) $prop->setNullable(false)->getType());


// a null initial value is folded into the type, in either order
$prop = (new Property('a'))->setType('int')->setValue(null);
Assert::same('?int', (string) $prop->getType());

$prop = (new Property('a'))->setValue(null)->setType('int');
Assert::same('?int', (string) $prop->getType());

$prop = (new Property('a'))->setType('int')->setInitialized();
Assert::same('?int', (string) $prop->getType());

$prop = (new Property('a'))->setType('int')->setValue(1)->setValue(null);
Assert::same('?int', (string) $prop->getType());

// an uninitialized property is not nullable
$prop = (new Property('a'))->setValue(null)->setInitialized(false)->setType('int');
Assert::same('int', (string) $prop->getType());
Assert::false($prop->isNullable());


// untyped property
$prop = new Property('a');
Assert::null($prop->getType());
Assert::false($prop->isNullable());
Assert::true($prop->setValue(null)->isNullable());

Assert::exception(
	fn() => (new Property('a'))->setNullable(),
	Nette\InvalidStateException::class,
	'Call setType() before setNullable().',
);
