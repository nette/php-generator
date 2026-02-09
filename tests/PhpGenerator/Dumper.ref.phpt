<?php declare(strict_types=1);

/**
 * Test: Nette\PhpGenerator\Dumper reference support
 */

use Nette\PhpGenerator\Dumper;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('ref=false ignores references', function () {
	$a = 'hello';
	$arr = [&$a, &$a];
	$dumper = new Dumper;
	Assert::same("['hello', 'hello']", $dumper->dump($arr));
});


test('ref=true single-use reference is not tracked', function () {
	$a = 42;
	$arr = [&$a];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same('[42]', $dumper->dump($arr));
});


test('ref=true shared reference', function () {
	$a = 'hello';
	$arr = [&$a, &$a];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same("(static function () { \$r[1] = 'hello'; return [&\$r[1], &\$r[1]]; })()", $dumper->dump($arr));
});


test('ref=true mixed references and plain values', function () {
	$a = 'ref';
	$arr = ['plain', &$a, 'also plain', &$a];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same("(static function () { \$r[1] = 'ref'; return ['plain', &\$r[1], 'also plain', &\$r[1]]; })()", $dumper->dump($arr));
});


test('ref=true with nested arrays', function () {
	$a = 42;
	$arr = [[&$a], [&$a]];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same('(static function () { $r[1] = 42; return [[&$r[1]], [&$r[1]]]; })()', $dumper->dump($arr));
});


test('ref=true with named keys', function () {
	$a = 'val';
	$arr = ['x' => &$a, 'y' => &$a];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same("(static function () { \$r[1] = 'val'; return ['x' => &\$r[1], 'y' => &\$r[1]]; })()", $dumper->dump($arr));
});


test('ref=true multiple reference groups', function () {
	$a = 'A';
	$b = 'B';
	$arr = [&$a, &$b, &$a, &$b];
	$dumper = new Dumper;
	$dumper->references = true;
	Assert::same("(static function () { \$r[1] = 'A'; \$r[2] = 'B'; return [&\$r[1], &\$r[2], &\$r[1], &\$r[2]]; })()", $dumper->dump($arr));
});


test('ref=true references reset between dump calls', function () {
	$dumper = new Dumper;
	$dumper->references = true;

	$a = 1;
	Assert::same('(static function () { $r[1] = 1; return [&$r[1], &$r[1]]; })()', $dumper->dump([&$a, &$a]));

	$b = 2;
	Assert::same('(static function () { $r[1] = 2; return [&$r[1], &$r[1]]; })()', $dumper->dump([&$b, &$b]));
});


test('ref=true cross-dependent values', function () {
	$a = 'x';
	$b = [1, 2, &$a];
	$c = [&$b, &$a, &$b];
	$dumper = new Dumper;
	$dumper->references = true;
	$result = $dumper->dump($c);
	Assert::contains('static function ()', $result);

	// verify the generated code recreates correct references
	$reconstructed = eval('return ' . $result . ';');
	$reconstructed[1] = 'changed';
	Assert::same('changed', $reconstructed[0][2]);
	Assert::same('changed', $reconstructed[2][2]);
	$reconstructed[0][0] = 99;
	Assert::same(99, $reconstructed[2][0]);
});


test('ref=true recursive reference', function () {
	$arr = [1, 2];
	$arr[2] = &$arr;
	$dumper = new Dumper;
	$dumper->references = true;
	$result = $dumper->dump($arr);
	Assert::contains('static function ()', $result);

	// verify recursive structure
	$reconstructed = eval('return ' . $result . ';');
	Assert::same(1, $reconstructed[0]);
	Assert::same(2, $reconstructed[1]);
	Assert::type('array', $reconstructed[2]);
});


test('ref=false throws on recursive array', function () {
	$arr = [1];
	$arr[1] = &$arr;
	$dumper = new Dumper;
	Assert::exception(
		fn() => $dumper->dump($arr),
		Nette\InvalidStateException::class,
		'%a%recursive%a%',
	);
});
