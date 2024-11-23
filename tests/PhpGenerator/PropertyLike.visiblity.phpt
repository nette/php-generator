<?php

/**
 * Test: PropertyLike visibility
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Demo');

// Default visibility (public)
$default = $class->addProperty('first')
	->setType('string');
Assert::true($default->isPublic());
Assert::false($default->isProtected());
Assert::false($default->isPrivate());
Assert::null($default->getVisibility());

// Explicit public
$public = $class->addProperty('second')
	->setType('string')
	->setPublic();
Assert::true($public->isPublic());
Assert::false($public->isProtected());
Assert::false($public->isPrivate());
Assert::same('public', $public->getVisibility());

// Protected
$protected = $class->addProperty('third')
	->setType('string')
	->setProtected();
Assert::false($protected->isPublic());
Assert::true($protected->isProtected());
Assert::false($protected->isPrivate());
Assert::same('protected', $protected->getVisibility());

// Private
$private = $class->addProperty('fourth')
	->setType('string')
	->setPrivate();
Assert::false($private->isPublic());
Assert::false($private->isProtected());
Assert::true($private->isPrivate());
Assert::same('private', $private->getVisibility());

// Change visibility
$changing = $class->addProperty('fifth')
	->setType('string')
	->setPublic();
$changing->setVisibility('protected');
Assert::false($changing->isPublic());
Assert::true($changing->isProtected());
Assert::false($changing->isPrivate());

// Test invalid visibility
Assert::exception(
	fn() => $changing->setVisibility('invalid'),
	Nette\InvalidArgumentException::class,
	'Argument must be public|protected|private.',
);

same(<<<'XX'
	class Demo
	{
		public string $first;
		public string $second;
		protected string $third;
		private string $fourth;
		protected string $fifth;
	}

	XX, (string) $class);
