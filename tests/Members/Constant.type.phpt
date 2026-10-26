<?php declare(strict_types=1);

/**
 * Test: type of a constant
 */

use Nette\PhpGenerator\Constant;
use Nette\Utils\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$const = (new Constant('A'))->setType('?int');
Assert::type(Type::class, $const->getType());
Assert::same('?int', (string) $const->getType());

$const = (new Constant('A'))->setType('int|null');
Assert::same('?int', (string) $const->getType());

$const = (new Constant('A'))->setType(Type::fromString('Foo|Bar'));
Assert::same('Foo|Bar', (string) $const->getType());

$const = new Constant('A');
Assert::null($const->getType());
Assert::null($const->setType('')->getType());

Assert::exception(
	fn() => (new Constant('A'))->setType('?A|B'),
	Nette\InvalidArgumentException::class,
);
