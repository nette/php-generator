<?php

/**
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.php80';

$res[] = ClassType::from(new Abc\Class8(null));

sameFile(__DIR__ . '/expected/ClassType.from.80.expect', implode("\n", $res));
