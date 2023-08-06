<?php

/**
 * Test: Nette\PhpGenerator\Dumper::dump() errors
 */

declare(strict_types=1);

use Nette\PhpGenerator\Dumper;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$rec = [];
	$rec[] = &$rec;
	$dumper = new Dumper;
	$dumper->dump($rec);
}, Nette\InvalidArgumentException::class, 'Nesting level too deep or recursive dependency.');


Assert::exception(function () {
	$rec = new stdClass;
	$rec->x = &$rec;
	$dumper = new Dumper;
	$dumper->dump($rec);
}, Nette\InvalidArgumentException::class, 'Nesting level too deep or recursive dependency.');
