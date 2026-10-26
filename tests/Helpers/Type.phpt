<?php declare(strict_types=1);

use Nette\PhpGenerator\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('nullable converts simple types', function () {
	Assert::same('?int', Type::nullable(Type::Int));
	Assert::same('int', Type::nullable(Type::Int, nullable: false));

	Assert::same('?int', Type::nullable('?int'));
	Assert::same('int', Type::nullable('?int', nullable: false));
});


test('nullable keeps null and mixed types', function () {
	Assert::same('null', Type::nullable('null'));
	Assert::same('NULL', Type::nullable('NULL'));
	Assert::same('null', Type::nullable('null', nullable: false));

	Assert::same('mixed', Type::nullable('mixed'));
	Assert::same('mixed', Type::nullable('mixed', nullable: false));
});


test('nullable handles union types', function () {
	Assert::same('int|float|string|null', Type::nullable('int|float|string'));
	Assert::same('int|float|string', Type::nullable('int|float|string', nullable: false));

	Assert::same('NULL|int|float|string', Type::nullable('NULL|int|float|string'));
	Assert::same('int|float|string', Type::nullable('NULL|int|float|string', nullable: false));

	Assert::same('int|float|string|null', Type::nullable('int|float|string|null'));
	Assert::same('int|float|string', Type::nullable('int|float|string|null', nullable: false));

	Assert::same('int|float|string|null', Type::nullable('int|float|null|string'));
	Assert::same('int|float|string', Type::nullable('int|float|null|string', nullable: false));
});


test('nullable handles intersection types', function () {
	Assert::same('(Foo&Bar)|null', Type::nullable('Foo&Bar'));
	Assert::same('Foo&Bar', Type::nullable('Foo&Bar', nullable: false));
	Assert::same('Foo&Bar', Type::nullable('(Foo&Bar)|null', nullable: false));
});


test('union combines types', function () {
	Assert::same('A|string', Type::union(A::class, Type::String));
});


test('intersection combines types', function () {
	Assert::same('A&string', Type::intersection(A::class, Type::String));
});
