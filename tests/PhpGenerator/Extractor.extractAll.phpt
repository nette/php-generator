<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.php')))->extractAll();
Assert::type(Nette\PhpGenerator\PhpFile::class, $file);
sameFile(__DIR__ . '/expected/Extractor.classes.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.81.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.classes.81.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.82.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.classes.82.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/enum.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.enum.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/traits.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.traits.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/bodies.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.bodies.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/extractor.php')))->extractAll();
sameFile(__DIR__ . '/expected/Extractor.expect', (string) $file);

Assert::exception(function () {
	(new Extractor(''));
}, Nette\InvalidStateException::class, 'The input string is not a PHP code.');
