<?php

declare(strict_types=1);

use Nette\PhpGenerator\Type;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';

// Nullable
Assert::same('?int', Type::nullable(Type::Int));
Assert::same('int', Type::nullable(Type::Int, nullable: false));

Assert::same('?int', Type::nullable('?int'));
Assert::same('int', Type::nullable('?int', nullable: false));

// TODO:
Assert::same('null', Type::nullable('null'));
Assert::same('...', Type::nullable('null', nullable: false));
Assert::same('mixed', Type::nullable('mixed'));
Assert::same('...', Type::nullable('mixed', nullable: false));


Assert::same('int|float|string|null', Type::nullable('int|float|string'));
Assert::same('int|float|string', Type::nullable('int|float|string', nullable: false));

Assert::same('NULL|int|float|string', Type::nullable('NULL|int|float|string'));
Assert::same('int|float|string', Type::nullable('NULL|int|float|string', nullable: false));

Assert::same('int|float|string|null', Type::nullable('int|float|string|null'));
Assert::same('int|float|string', Type::nullable('int|float|string|null', nullable: false));

Assert::same('int|float|null|string', Type::nullable('int|float|null|string'));
Assert::same('int|float|string', Type::nullable('int|float|null|string', nullable: false));

Assert::exception(
	fn() => Type::nullable('Foo&Bar'),
	Nette\InvalidArgumentException::class,
	'Intersection types cannot be nullable.',
);
Assert::same('Foo&Bar', Type::nullable('Foo&Bar', nullable: false));


// Union
Assert::same('A|string', Type::union(A::class, Type::String));

// Intersection
Assert::same('A&string', Type::intersection(A::class, Type::String));
