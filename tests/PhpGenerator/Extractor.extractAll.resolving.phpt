<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;

require __DIR__ . '/../bootstrap.php';


$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/bodies.php')))->extractAll();
$classes = $file->getClasses();

$namespace = new PhpNamespace('Nette');
$namespace->addUse('Abc\a\FOO'); // must not be confused with constant
$namespace->addUse('Abc\a\func'); // must not be confused with func
$namespace->add(reset($classes));

$printer = new Printer;
sameFile(__DIR__ . '/expected/Factory.fromCode.bodies.resolving.expect', $printer->printNamespace($namespace));
