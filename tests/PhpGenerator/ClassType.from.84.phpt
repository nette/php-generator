<?php

/**
 * @phpVersion 8.4
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.84.php';

$res[] = ClassType::from(Abc\PropertyHookSignatures::class);
$res[] = ClassType::from(Abc\AbstractHookSignatures::class);
$res[] = InterfaceType::from(Abc\InterfaceHookSignatures::class);
$res[] = ClassType::from(Abc\AsymmetricVisibilitySignatures::class);
$res[] = ClassType::from(Abc\CombinedSignatures::class);
$res[] = ClassType::from(Abc\ConstructorAllSignatures::class);

sameFile(__DIR__ . '/expected/ClassType.from.84.expect', implode("\n", $res));
