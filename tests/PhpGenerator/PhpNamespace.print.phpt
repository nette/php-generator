<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;

require __DIR__ . '/../bootstrap.php';


$namespace = new PhpNamespace('Foo');

$namespace->addUse('Foo');
$namespace->addUse('Bar\C');
$namespace->addUseFunction('Bar\c');
$namespace->addUseConstant('Bar\FOO');

$classA = $namespace->addClass('A');
$interfaceB = $namespace->addInterface('B');

$classA
	->addImplement('Foo\A')
	->addImplement('Bar\C')
	->addTrait('Bar\D')
	->addAttribute('Foo\A');

$method = $classA->addMethod('test');
$method->addAttribute('Foo\A');
$method->setReturnType('static|Foo\A');

$method->addParameter('a')->setType('Bar\C')->addAttribute('Bar\D');
$method->addParameter('b')->setType('self');
$method->addParameter('c')->setType('parent');
$method->addParameter('d')->setType('array');
$method->addParameter('e')->setType('?callable');
$method->addParameter('f')->setType('Bar\C|string');

sameFile(__DIR__ . '/expected/PhpNamespace.expect', (string) $namespace);
