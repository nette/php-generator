<?php

declare(strict_types=1);

use Nette\PhpGenerator\InterfaceType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = InterfaceType::fromCode(file_get_contents(__DIR__ . '/fixtures/classes.php'));
Assert::type(InterfaceType::class, $class);
Assert::match(<<<'XX'
	/**
	 * Interface
	 * @author John Doe
	 */
	interface Interface1
	{
		function func1();
	}
	XX, (string) $class);


Assert::exception(function () {
	InterfaceType::fromCode('<?php');
}, Nette\InvalidStateException::class, 'The code does not contain any class.');
