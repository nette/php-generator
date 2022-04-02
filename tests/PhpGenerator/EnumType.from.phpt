<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\PhpGenerator\EnumType;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/enum.php';

$res[] = EnumType::from(Abc\Enum1::class);
$res[] = EnumType::from(Abc\Enum2::class);

sameFile(__DIR__ . '/expected/EnumType.from.expect', implode("\n", $res));
