<?php

/**
 * Test: PHP 8.4 property hooks for classes
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PropertyHookType;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Demo');

$class->addProperty('first')
	->setType('string')
	->setValue('x')
	->setPublic()
	->addHook(PropertyHookType::Set)
		->setBody('$value . ?', ['x'], short: true)
		->addComment('comment')
		->addAttribute('Example')
		->addParameter('value')
			->setType('string');

$prop = $class->addProperty('second')
	->setType('string')
	->setPublic();

$prop->addHook('get')
	->setBody('return $this->second;')
	->setReturnReference()
	->setFinal();

$prop->addHook('set', '$value')
	->addParameter('value')
		->setType('string');

same(<<<'XX'
	class Demo
	{
		public string $first = 'x' {
			/** comment */
			#[Example]
			set(string $value) => $value . 'x';
		}

		public string $second {
			set(string $value) => $value;
			final &get {
				return $this->second;
			}
		}
	}

	XX, (string) $class);



// promoted properties

$class = new ClassType('Demo');

$method = $class->addMethod('__construct');

$method->addPromotedParameter('first')
	->setType('string')
	->addHook('get')
		->setBody('return $this->first . "x";')
		->setReturnReference();

$method->addPromotedParameter('second')
	->setType('string')
	->addHook('set', '$value')
		->setFinal()
		->addParameter('value')
			->setType('string');

$method->addPromotedParameter('third')
	->setPublic()
	->setProtected('set')
	->setFinal()
	->setType('string')
	->addComment('hello')
	->addAttribute('Example');

same(<<<'XX'
	class Demo
	{
		public function __construct(
			public string $first {
				&get {
					return $this->first . "x";
				}
			},
			public string $second {
				final set(string $value) => $value;
			},
			/** hello */
			#[Example]
			final public protected(set) string $third,
		) {
		}
	}

	XX, (string) $class);


$interface = new InterfaceType('Demo');

$interface->addProperty('first')
	->setType('int')
	->setPublic()
	->addHook('get');

$prop = $interface->addProperty('second')
	->setType('Value')
	->setPublic();

$prop->addHook('get');
$prop->addHook('set');

same(<<<'XX'
	interface Demo
	{
		public int $first { get; }
		public Value $second { set; get; }
	}

	XX, (string) $interface);
