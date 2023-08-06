<?php

/**
 * @phpVersion 8.2
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Extractor;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/fixtures/classes.82.php';

$class = ClassType::from(new Abc\Class13);
Assert::false($class->getProperty('foo')->isReadOnly());
Assert::false($class->getMethod('__construct')->getParameter('bar')->isReadOnly());

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.82.php')))->extractAll();
$class = $file->getClasses()[Abc\Class13::class];
Assert::false($class->getProperty('foo')->isReadOnly());
Assert::false($class->getMethod('__construct')->getParameter('bar')->isReadOnly());
