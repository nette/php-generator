<?php declare(strict_types=1);

/**
 * Test: nullable parameter
 */

use Nette\PhpGenerator\{Method, Parameter};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::error(
	fn() => (new Parameter('a'))->setNullable(),
	E_USER_DEPRECATED,
	'Calling setNullable() before setType() is deprecated, set the type first.',
);

$method = new Method('f');
$param = $method->addParameter('a')->setType('int')->setNullable();
Assert::true($param->isNullable());
Assert::same("function f(?int \$a)\n{\n}\n", (string) $method);
