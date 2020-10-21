<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;


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

sameFile(__DIR__ . '/expected/ClassType.promotion.expect', (string) $class);
