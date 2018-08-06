<?php

declare(strict_types=1);

use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;

require __DIR__ . '/../bootstrap.php';


$printer = new Printer;


$namespace = new PhpNamespace('Foo');
$namespace->addUse('Bar\C');

$class = $namespace->addClass('A')
	->setFinal(true)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addImplement('Foo\IOne')
	->setTraits(['Foo\ObjectTrait'])
	->addComment("Description of class.\nThis is example\n");

$class->addMethod('first')
	->addComment('@return resource')
	->setFinal(true)
	->setReturnType('stdClass')
	->setBody('return $this->?;', ['handle'])
	->addParameter('var')
		->setTypeHint('Bar\C');


sameFile(__DIR__ . '/expected/Printer.namespace.expect', $printer->printNamespace($namespace));
sameFile(__DIR__ . '/expected/Printer.namespace.class.expect', $printer->printClass($class, $namespace));
sameFile(__DIR__ . '/expected/Printer.namespace.class2.expect', $printer->printClass($class));
sameFile(__DIR__ . '/expected/Printer.namespace.method.expect', $printer->printMethod($class->getMethod('first')));


$function = new \Nette\PhpGenerator\GlobalFunction('func');
$function
	->setReturnType('stdClass')
	->setBody('return 123;')
	->addParameter('var')
		->setTypeHint('Bar\C');

sameFile(__DIR__ . '/expected/Printer.namespace.function.expect', $printer->printFunction($function, $namespace));
