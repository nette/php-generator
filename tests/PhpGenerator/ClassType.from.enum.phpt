<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/enum.php';

$res[] = ClassType::from(Abc\Enum1::class);
$res[] = ClassType::from(Abc\Enum2::class);

sameFile(__DIR__ . '/expected/ClassType.from.enum.expect', implode("\n", $res));
