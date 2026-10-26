<?php declare(strict_types=1);

/**
 * Test: nullable property
 */

use Nette\PhpGenerator\{ClassType, Property};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::error(
	fn() => (new Property('a'))->setNullable(),
	E_USER_DEPRECATED,
	'Calling setNullable() before setType() is deprecated, set the type first.',
);

$class = new ClassType('A');
$prop = $class->addProperty('a')->setType('int')->setNullable();
Assert::true($prop->isNullable());
Assert::match('%A%public ?int $a;%A%', (string) $class);
