<?php

/**
 * @phpVersion 8.5
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.85.php';

$res[] = ClassType::from(Abc\Class85::class);

sameFile(__DIR__ . '/expected/ClassType.from.85.expect', implode("\n", $res));
