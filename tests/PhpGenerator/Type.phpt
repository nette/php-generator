<?php

declare(strict_types=1);

use Nette\PhpGenerator\Type;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


Assert::same('A|string', Type::union(A::class, Type::String));

Assert::same('?A', Type::nullable(A::class));
Assert::same('?A', Type::nullable(A::class));
Assert::same('A', Type::nullable(A::class, nullable: false));

Assert::same('?A', Type::nullable('?A'));
Assert::same('A', Type::nullable('?A', nullable: false));
