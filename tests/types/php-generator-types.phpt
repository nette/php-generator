<?php declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Nette\PHPStan\Tester\TypeAssert;

TypeAssert::assertTypes(__DIR__ . '/php-generator-types.php');
