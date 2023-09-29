<?php

/**
 * Test: Nette\PhpGenerator for classes.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Example');

$class
	->addComment('Description of class.')
	->addAttribute('ExampleAttribute')
	->addAttribute('WithArgument', [new Literal('Foo::BAR')])
	->addAttribute('Table', [
		'name' => 'user',
		'constraints' => [
			Literal::new('UniqueConstraint', ['name' => 'ean', 'columns' => ['ean']]),
		],
	]);

$class->addConstant('FOO', 123)
	->addComment('Commented')
	->addAttribute('ExampleAttribute')
	->addAttribute('WithArguments', [true]);

$class->addProperty('handle')
	->addComment('@var resource')
	->addAttribute('ExampleAttribute');

$method = $class->addMethod('getHandle')
	->addComment('Returns file handle.')
	->addAttribute('ExampleAttribute');

$method->addParameter('mode')
	->addComment('comment')
	->addAttribute('ExampleAttribute')
	->addAttribute('WithArguments', [0]);

sameFile(__DIR__ . '/expected/ClassType.attributes.expect', (string) $class);
