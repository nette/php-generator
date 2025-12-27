<?php declare(strict_types=1);

use Nette\PhpGenerator\{ClassType, InterfaceType};

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../fixtures/classes.83.php';

$res[] = ClassType::from(Abc\Class14::class);
$res[] = InterfaceType::from(Abc\Interface14::class);

sameFile(__DIR__ . '/expected/ClassType.from.83.expect', implode("\n", $res));
