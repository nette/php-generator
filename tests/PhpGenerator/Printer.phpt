<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\Printer;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$printer = new Printer;


$class = (new ClassType('Example'))
	->setFinal(true)
	->setExtends('ParentClass')
	->addImplement('IExample')
	->setTraits(['ObjectTrait'])
	->addTrait('AnotherTrait', ['sayHello as protected'])
	->addComment("Description of class.\nThis is example\n");

$class->addConstant('FORCE_ARRAY', new PhpLiteral('Nette\Utils\Json::FORCE_ARRAY'))
	->setVisibility('private')
	->addComment('Commented');

$class->addProperty('handle')
	->setVisibility('private')
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new PhpLiteral('RecursiveIteratorIterator::SELF_FIRST'));

$class->addMethod('first')
	->addComment('@return resource')
	->setFinal(true)
	->setReturnType('stdClass')
	->setBody('return $this->?;', ['handle'])
	->addParameter('var')
		->setTypeHint('stdClass');

$class->addMethod('second');


sameFile(__DIR__ . '/expected/Printer.class.expect', $printer->printClass($class));
sameFile(__DIR__ . '/expected/Printer.method.expect', $printer->printMethod($class->getMethod('first')));


$function = new \Nette\PhpGenerator\GlobalFunction('func');
$function
	->setReturnType('stdClass')
	->setBody('return 123;')
	->addParameter('var')
		->setTypeHint('stdClass');

sameFile(__DIR__ . '/expected/Printer.function.expect', $printer->printFunction($function));


$closure = new \Nette\PhpGenerator\Closure;
$closure
	->setReturnType('stdClass')
	->setBody('return 123;')
	->addParameter('var')
		->setTypeHint('stdClass');

sameFile(__DIR__ . '/expected/Printer.closure.expect', $printer->printClosure($closure));


// printer validates class
Assert::exception(function () {
	$class = new ClassType;
	$class->setFinal(true)->setAbstract(true);
	(new Printer)->printClass($class);
}, Nette\InvalidStateException::class, 'Class cannot be abstract and final.');
