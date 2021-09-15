<?php

/**
 * @phpVersion 7.4
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.74.php';

$res[] = ClassType::from(new Abc\Class7);

sameFile(__DIR__ . '/expected/ClassType.from.74.expect', implode("\n", $res));
