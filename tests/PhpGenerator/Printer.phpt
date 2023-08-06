<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$printer = new Printer;


$class = (new ClassType('Example'))
	->setFinal()
	->setExtends('ParentClass')
	->addImplement('IExample')
	->addComment("Description of class.\nThis is example\n");

$class->addTrait('ObjectTrait');
$class->addTrait('AnotherTrait')
	->addResolution('sayHello as protected');

$class->addConstant('FORCE_ARRAY', new Literal('Nette\Utils\Json::FORCE_ARRAY'))
	->setPrivate()
	->addComment('Commented');

$class->addConstant('MULTILINE_LONG', ['aaaaaaaa' => 1, 'bbbbbbbb' => 2, 'cccccccc' => 3, 'dddddddd' => 4, 'eeeeeeee' => 5, 'ffffffff' => 6]);
$class->addConstant('SHORT', ['aaaaaaaa' => 1, 'bbbbbbbb' => 2, 'cccccccc' => 3, 'dddddddd' => 4, 'eeeeeeee' => 5]);

$class->addProperty('handle')
	->setVisibility('private')
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new Literal('RecursiveIteratorIterator::SELF_FIRST'));

$class->addProperty('multilineLong', ['aaaaaaaa' => 1, 'bbbbbbbb' => 2, 'cccccccc' => 3, 'dddddddd' => 4, 'eeeeeeee' => 5, 'ffffffff' => 6]);
$class->addProperty('short', ['aaaaaaaa' => 1, 'bbbbbbbb' => 2, 'cccccccc' => 3, 'dddddddd' => 4, 'eeeeeeee' => 5, 'ffffffff' => 6]);

$class->addMethod('first')
	->addComment('@return resource')
	->setFinal()
	->setReturnType('stdClass')
	->setBody("func(); \r\nreturn ?;", [['aaaaaaaaaaaa' => 1, 'bbbbbbbbbbb' => 2, 'cccccccccccccc' => 3, 'dddddddddddd' => 4, 'eeeeeeeeeeee' => 5, 'ffffffff' => 6]])
	->addParameter('var')
		->setType('stdClass');

$class->addMethod('second');

$method = $class->addMethod('multi')
	->addParameter('foo')
		->addAttribute('Foo');

$method = $class->addMethod('multiType')
	->setReturnType('array')
	->addParameter('foo')
		->addAttribute('Foo');


sameFile(__DIR__ . '/expected/Printer.class.expect', $printer->printClass($class));
sameFile(__DIR__ . '/expected/Printer.method.expect', $printer->printMethod($class->getMethod('first')));


$printer->linesBetweenProperties = 1;
$printer->linesBetweenMethods = 3;
$printer->bracesOnNextLine = false;
sameFile(__DIR__ . '/expected/Printer.class-alt.expect', $printer->printClass($class));



$closure = new Nette\PhpGenerator\Closure;
$closure
	->setReturnType('stdClass')
	->setBody("func(); \r\nreturn 123;")
	->addParameter('var')
		->setType('stdClass');

sameFile(__DIR__ . '/expected/Printer.closure.expect', $printer->printClosure($closure));


// printer validates class
Assert::exception(function () {
	$class = new ClassType;
	$class->setFinal()->setAbstract();
	(new Printer)->printClass($class);
}, Nette\InvalidStateException::class, 'Anonymous class cannot be abstract or final.');
