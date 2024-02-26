<?php

/**
 * @phpVersion 8.2
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\TraitType;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.82.php';

$res[] = ClassType::from(new Abc\Class13);
$res[] = TraitType::from(Abc\Trait13::class);

sameFile(__DIR__ . '/expected/ClassType.from.82.expect', implode("\n", $res));
