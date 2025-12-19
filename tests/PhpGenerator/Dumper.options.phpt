<?php declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('maxDepth limits recursion depth', function () {
	$dumper = new Dumper;
	$dumper->maxDepth = 2;

	// level 0: outer array
	// level 1: ['level1' => ...]
	// level 2: ['level2' => ...]
	// level 3: ['level3' => ...] <- this exceeds maxDepth=2
	$data = [
		'level1' => [
			'level2' => [
				'level3' => [
					'level4' => 'too deep',
				],
			],
		],
	];

	Assert::exception(
		fn() => $dumper->dump($data),
		Nette\InvalidStateException::class,
		'Nesting level too deep or recursive dependency.',
	);
});


test('maxDepth works with objects', function () {
	$dumper = new Dumper;
	$dumper->maxDepth = 1;

	// level 0: outer object
	// level 1: $obj->nested (stdClass)
	// level 2: $obj->nested->deeper <- exceeds maxDepth=1
	$obj = new stdClass;
	$obj->nested = new stdClass;
	$obj->nested->deeper = new stdClass;

	Assert::exception(
		fn() => $dumper->dump($obj),
		Nette\InvalidStateException::class,
		'Nesting level too deep or recursive dependency.',
	);
});


test('maxDepth allows exact depth', function () {
	$dumper = new Dumper;
	$dumper->maxDepth = 3;

	$data = [
		'level1' => [
			'level2' => [
				'level3' => 'ok',
			],
		],
	];

	$result = $dumper->dump($data);
	Assert::contains("'level3' => 'ok'", $result);
});


test('wrapLength affects array wrapping', function () {
	$dumper = new Dumper;
	$dumper->wrapLength = 30;

	$data = ['veryLongKey' => 'some longer value that exceeds wrap length'];

	$result = $dumper->dump($data);

	// With short wrapLength, should wrap to multiple lines
	Assert::contains("\n", $result);
});


test('wrapLength with very short value stays inline', function () {
	$dumper = new Dumper;
	$dumper->wrapLength = 100;

	$data = ['a' => 1];

	$result = $dumper->dump($data);

	// Should be on single line
	Assert::same("['a' => 1]", $result);
});


test('format() with placeholders', function () {
	$dumper = new Dumper;

	$result = $dumper->format('new Class(?, ?, ?)', 1, 'string', ['array']);

	Assert::same("new Class(1, 'string', ['array'])", $result);
});


test('format() with spread arguments', function () {
	$dumper = new Dumper;

	$result = $dumper->format('new Class(...?)', [1, 2, 3]);

	Assert::same('new Class(1, 2, 3)', $result);
});


test('format() with Literal', function () {
	$dumper = new Dumper;

	$result = $dumper->format('return ?;', new Literal('$this->value'));

	Assert::same('return $this->value;', $result);
});


test('dump() handles circular reference in arrays', function () {
	$dumper = new Dumper;

	$a = ['self' => null];
	$a['self'] = &$a;

	Assert::exception(
		fn() => $dumper->dump($a),
		Nette\InvalidStateException::class,
		'Nesting level too deep or recursive dependency.',
	);
});


test('dump() handles circular reference in objects', function () {
	$dumper = new Dumper;

	$obj = new stdClass;
	$obj->self = $obj;

	Assert::exception(
		fn() => $dumper->dump($obj),
		Nette\InvalidStateException::class,
		'Nesting level too deep or recursive dependency.',
	);
});
