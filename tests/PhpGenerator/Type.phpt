<?php

declare(strict_types=1);

use Nette\PhpGenerator\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::same('A|string', Type::union(A::class, Type::STRING));

Assert::same('?A', Type::nullable(A::class));
Assert::same('?A', Type::nullable(A::class, true));
Assert::same('A', Type::nullable(A::class, false));

Assert::same('?A', Type::nullable('?A', true));
Assert::same('A', Type::nullable('?A', false));

Assert::same(stdClass::class, Type::getType(new stdClass));
Assert::same(Type::STRING, Type::getType(''));
Assert::same(Type::INT, Type::getType(1));
Assert::same(Type::FLOAT, Type::getType(1.0));
Assert::same(Type::ARRAY, Type::getType([]));
Assert::same(null, Type::getType(fopen(__FILE__, 'r')));
