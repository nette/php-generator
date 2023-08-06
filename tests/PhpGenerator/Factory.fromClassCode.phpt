<?php

declare(strict_types=1);

use Nette\PhpGenerator\Factory;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$factory = new Factory;

$class = $factory->fromClassCode(file_get_contents(__DIR__ . '/fixtures/classes.php'));
Assert::type(Nette\PhpGenerator\InterfaceType::class, $class);
Assert::match(<<<'XX'
	/**
	 * Interface
	 * @author John Doe
	 */
	interface Interface1
	{
		public function func1();
	}
	XX, (string) $class);
