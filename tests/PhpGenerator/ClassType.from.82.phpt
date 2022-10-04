<?php

/**
 * @phpVersion 8.2
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.82.php';

$res[] = ClassType::from(new Abc\Class13);

sameFile(__DIR__ . '/expected/ClassType.from.82.expect', implode("\n", $res));
