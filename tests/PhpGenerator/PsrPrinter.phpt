<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\PhpGenerator\PsrPrinter;


require __DIR__ . '/../bootstrap.php';


$printer = new PsrPrinter;


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

$class->addConstant('MULTILINE_LONG', ['aaaaaaa' => 1, 'bbbbbbb' => 2, 'ccccccc' => 3, 'ddddddd' => 4, 'eeeeee' => 5]);
$class->addConstant('SHORT', ['aaaaaaa' => 1, 'bbbbbbb' => 2, 'ccccccc' => 3, 'ddddddd' => 4, 'eeeeee' => 5]);

$class->addProperty('handle')
	->setVisibility('private')
	->addComment('@var resource  orignal file handle');

$class->addProperty('order')
	->setValue(new PhpLiteral('RecursiveIteratorIterator::SELF_FIRST'));

$class->addProperty('multilineLong', ['aaaaaaa' => 1, 'bbbbbbb' => 2, 'ccccccc' => 3, 'ddddddd' => 4, 'eeeeee' => 5]);
$class->addProperty('short', ['aaaaaaa' => 1, 'bbbbbbb' => 2, 'ccccccc' => 3, 'ddddddd' => 4, 'eeeeee' => 5]);

$class->addMethod('first')
	->addComment('@return resource')
	->setFinal(true)
	->setReturnType('stdClass')
	->setBody("func();\nreturn ?;", [['aaaaaaaaaaaa' => 1, 'bbbbbbbbbbb' => 2, 'cccccccccccccc' => 3, 'dddddddddddd' => 4, 'eeeeeeeeeeee' => 5]])
	->addParameter('var')
		->setType('stdClass');

$class->addMethod('second');


sameFile(__DIR__ . '/expected/PsrPrinter.class.expect', $printer->printClass($class));
