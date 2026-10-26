<?php declare(strict_types=1);

/**
 * Test: nullable return type
 */

use Nette\PhpGenerator\Method;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::error(
	fn() => (new Method('f'))->setReturnNullable(),
	E_USER_DEPRECATED,
	'Calling setReturnNullable() before setReturnType() is deprecated, set the type first.',
);

$method = (new Method('f'))->setReturnType('int')->setReturnNullable();
Assert::true($method->isReturnNullable());
Assert::same("function f(): ?int\n{\n}\n", (string) $method);
