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


test('nullable handles null type', function () {
	Assert::same('null', Type::nullable('null'));
	Assert::same('NULL', Type::nullable('NULL'));

	Assert::exception(
		fn() => Type::nullable('null', nullable: false),
		Nette\InvalidArgumentException::class,
		'Type null cannot be not nullable.',
	);
});


test('nullable handles mixed type', function () {
	Assert::same('mixed', Type::nullable('mixed'));

	Assert::exception(
		fn() => Type::nullable('mixed', nullable: false),
		Nette\InvalidArgumentException::class,
		'Type mixed cannot be not nullable.',
	);
});


test('nullable handles union types', function () {
	Assert::same('int|float|string|null', Type::nullable('int|float|string'));
	Assert::same('int|float|string', Type::nullable('int|float|string', nullable: false));

	Assert::same('NULL|int|float|string', Type::nullable('NULL|int|float|string'));
	Assert::same('int|float|string', Type::nullable('NULL|int|float|string', nullable: false));

	Assert::same('int|float|string|null', Type::nullable('int|float|string|null'));
	Assert::same('int|float|string', Type::nullable('int|float|string|null', nullable: false));

	Assert::same('int|float|null|string', Type::nullable('int|float|null|string'));
	Assert::same('int|float|string', Type::nullable('int|float|null|string', nullable: false));
});


test('nullable handles intersection types', function () {
	Assert::exception(
		fn() => Type::nullable('Foo&Bar'),
		Nette\InvalidArgumentException::class,
		'Intersection types cannot be nullable.',
	);
	Assert::same('Foo&Bar', Type::nullable('Foo&Bar', nullable: false));
});


test('union combines types', function () {
	Assert::same('A|string', Type::union(A::class, Type::String));
});


test('intersection combines types', function () {
	Assert::same('A&string', Type::intersection(A::class, Type::String));
});
