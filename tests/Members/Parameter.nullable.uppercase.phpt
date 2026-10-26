<?php declare(strict_types=1);

/**
 * Test: built-in type names are case-insensitive in nullability
 */

use Nette\PhpGenerator\Parameter;
use Nette\Utils\Type;
use Tester\{Assert, Environment};

require __DIR__ . '/../bootstrap.php';

if (!Type::fromString('MIXED')->allows('null')) {
	Environment::skip('requires nette/utils with case-insensitive built-in type names');
}


$param = (new Parameter('a'))->setType('MIXED')->setDefaultValue(null);
Assert::same('MIXED', (string) $param->getType());
Assert::true($param->isNullable());
