<?php

/**
 * Test: Nette\PhpGenerator - PHP 8.4 property hooks for interfaces
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\Type;
use Tester\Assert;
use Nette\PhpGenerator\Closure;

require __DIR__ . '/../bootstrap.php';

$file = new PhpFile;
$file->setStrictTypes();

$namespace = $file->addNamespace('Abc');

$interface = new InterfaceType('HasAuthor');

// This will not be printed because it does not have any hooks
$interface->addProperty('isVisible')
    ->setType(Type::Bool)
    ->setPublic();

$interface->addProperty('score')
    ->setType(Type::Int)
    ->setPublic()
    ->setGetHook(new Closure());

$interface->addProperty('author')
    ->setType('Author')
    ->setPublic()
    ->setGetHook(new Closure())
    ->setSetHook(new Closure());

$expected = <<<'PHP'
interface HasAuthor
{
    public int $score { get; }
    public Author $author { get; set; }
}
PHP;

same(rtrim($expected), rtrim((new PsrPrinter)->printClass($interface)));
