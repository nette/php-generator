<?php declare(strict_types=1);

/**
 * @phpVersion 8.5
 */

use Nette\PhpGenerator\ClassType;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.85.php';

$res[] = ClassType::from(Abc\Class85::class);

sameFile(__DIR__ . '/expected/ClassType.from.85.expect', implode("\n", $res));
