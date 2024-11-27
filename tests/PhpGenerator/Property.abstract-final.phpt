<?php

/**
 * Test: PHP 8.4 abstract/final property
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;

require __DIR__ . '/../bootstrap.php';


$class = (new ClassType('Demo'))
	->setAbstract();

$class->addProperty('first')
	->setType('string')
	->setAbstract()
	->addHook('set')
		->setAbstract();

$prop = $class->addProperty('second')
	->setType('string')
	->setAbstract();

$prop->addHook('set')
	->setAbstract();

$prop->addHook('get', '123');

$class->addProperty('third')
	->setFinal();

same(<<<'XX'
	abstract class Demo
	{
		abstract public string $first { set; }

		abstract public string $second {
			set;
			get => 123;
		}

		final public $third;
	}

	XX, (string) $class);
