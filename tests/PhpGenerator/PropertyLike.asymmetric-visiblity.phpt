<?php

/**
 * Test: PropertyLike asymmetric visibility
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PropertyAccessMode;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$class = new ClassType('Demo');

// Default visibility
$default = $class->addProperty('first')
	->setType('string');
Assert::true($default->isPublic(PropertyAccessMode::Get));
Assert::true($default->isPublic(PropertyAccessMode::Set));
Assert::null($default->getVisibility());
Assert::null($default->getVisibility('set'));

// Public with private setter
$restricted = $class->addProperty('second')
	->setType('string')
	->setVisibility(null, 'private');
Assert::true($restricted->isPublic());
Assert::false($restricted->isPublic('set'));
Assert::true($restricted->isPrivate('set'));
Assert::null($restricted->getVisibility());
Assert::same('private', $restricted->getVisibility('set'));

// Public with protected setter using individual methods
$mixed = $class->addProperty('third')
	->setType('string')
	->setPublic()
	->setProtected('set');
Assert::true($mixed->isPublic());
Assert::false($mixed->isPublic('set'));
Assert::true($mixed->isProtected('set'));
Assert::same('public', $mixed->getVisibility());
Assert::same('protected', $mixed->getVisibility('set'));

// Protected with private setter
$nested = $class->addProperty('fourth')
	->setType('string')
	->setProtected()
	->setPrivate('set');
Assert::false($nested->isPublic());
Assert::true($nested->isProtected());
Assert::true($nested->isPrivate('set'));
Assert::same('protected', $nested->getVisibility());
Assert::same('private', $nested->getVisibility('set'));

// Test invalid getter visibility
Assert::exception(
	fn() => $default->setVisibility('invalid', 'public'),
	ValueError::class,
);

// Test invalid setter visibility
Assert::exception(
	fn() => $default->setVisibility('public', 'invalid'),
	ValueError::class,
);


same(<<<'XX'
	class Demo
	{
		public string $first;
		private(set) string $second;
		public protected(set) string $third;
		protected private(set) string $fourth;
	}

	XX, (string) $class);
