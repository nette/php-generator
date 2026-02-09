<?php declare(strict_types=1);

/**
 * Test: Nette\PhpGenerator\Dumper context validation
 */

use Nette\PhpGenerator\DumpContext;
use Nette\PhpGenerator\Dumper;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


// Constant context (class constant, property default)

test('Constant context rejects stdClass', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Constant;
	Assert::exception(
		fn() => $dumper->dump((object) ['a' => 1]),
		Nette\InvalidStateException::class,
		'%a% stdClass %a% Constant %a%',
	);
});


test('Constant context rejects DateTime', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Constant;
	Assert::exception(
		fn() => $dumper->dump(new DateTime('2024-01-01')),
		Nette\InvalidStateException::class,
		'%a% DateTime %a% Constant %a%',
	);
});


test('Constant context rejects custom objects', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Constant;
	Assert::exception(
		fn() => $dumper->dump(new ArrayObject),
		Nette\InvalidStateException::class,
		'%a% ArrayObject %a% Constant %a%',
	);
});


test('Constant context allows first-class callable', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Constant;
	Assert::contains('(...)', $dumper->dump(strlen(...)));
});


// Property context (same restrictions as Constant)

test('Property context rejects stdClass', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Property;
	Assert::exception(
		fn() => $dumper->dump((object) ['a' => 1]),
		Nette\InvalidStateException::class,
		'%a% Property %a%',
	);
});


test('Property context rejects DateTime', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Property;
	Assert::exception(
		fn() => $dumper->dump(new DateTime('2024-01-01')),
		Nette\InvalidStateException::class,
		'%a% Property %a%',
	);
});


test('Property context rejects custom objects', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Property;
	Assert::exception(
		fn() => $dumper->dump(new ArrayObject),
		Nette\InvalidStateException::class,
		'%a% Property %a%',
	);
});


// Parameter context (allows new, casts)

test('Parameter context allows stdClass', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Parameter;
	Assert::contains('(object)', $dumper->dump((object) ['a' => 1]));
});


test('Parameter context allows DateTime', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Parameter;
	Assert::contains('new \DateTime', $dumper->dump(new DateTime('2024-01-01')));
});


test('Parameter context rejects custom objects', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Parameter;
	Assert::exception(
		fn() => $dumper->dump(new ArrayObject),
		Nette\InvalidStateException::class,
		'%a% ArrayObject %a% Parameter %a%',
	);
});


// Attribute context (same as Parameter)

test('Attribute context allows stdClass', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Attribute;
	Assert::contains('(object)', $dumper->dump((object) ['a' => 1]));
});


test('Attribute context allows DateTime', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Attribute;
	Assert::contains('new \DateTime', $dumper->dump(new DateTime('2024-01-01')));
});


test('Attribute context rejects custom objects', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Attribute;
	Assert::exception(
		fn() => $dumper->dump(new ArrayObject),
		Nette\InvalidStateException::class,
		'%a% ArrayObject %a% Attribute %a%',
	);
});


// Expression context (no restrictions, default)

test('Expression context allows everything', function () {
	$dumper = new Dumper;
	Assert::same(DumpContext::Expression, $dumper->context);
	Assert::contains('(object)', $dumper->dump((object) ['a' => 1]));
	Assert::contains('new \DateTime', $dumper->dump(new DateTime('2024-01-01')));
	Assert::contains('createObject', $dumper->dump(new ArrayObject));
});


// Propagation through arrays

test('context propagates into array elements', function () {
	$dumper = new Dumper;
	$dumper->context = DumpContext::Constant;
	Assert::exception(
		fn() => $dumper->dump(['key' => (object) ['a' => 1]]),
		Nette\InvalidStateException::class,
		'%a% stdClass %a% Constant %a%',
	);
});
