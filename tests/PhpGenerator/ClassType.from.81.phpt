<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.81.php';

$res[] = ClassType::from(new Abc\Class11);

sameFile(__DIR__ . '/expected/ClassType.from.81.expect', implode("\n", $res));
