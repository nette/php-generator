<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;


require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');
$method = $class->addMethod('__construct');
$method->addParameter('a');
$method->addPromotedParameter('b');
$method->addPromotedParameter('c')
	->setPrivate()
	->setType('string')
	->addComment('promo')
	->addAttribute('Example');

$method->addPromotedParameter('d', new Literal('new Draft(?)', [10]))
	->setType('Draft')
	->setReadOnly();

sameFile(__DIR__ . '/expected/ClassType.promotion.expect', (string) $class);
