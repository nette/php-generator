<?php declare(strict_types=1);

/**
 * Test: PropertyLike asymmetric visibility
 */

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PropertyAccessMode;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('default property visibility is public for both get and set', function () {
	$class = new ClassType('Demo');
	$default = $class->addProperty('first')
		->setType('string');
	Assert::true($default->isPublic(PropertyAccessMode::Get));
	Assert::true($default->isPublic(PropertyAccessMode::Set));
	Assert::null($default->getVisibility());
	Assert::null($default->getVisibility('set'));
});


test('property with public getter and private setter', function () {
	$class = new ClassType('Demo');
	$restricted = $class->addProperty('second')
		->setType('string')
		->setVisibility(null, 'private');
	Assert::true($restricted->isPublic());
	Assert::false($restricted->isPublic('set'));
	Assert::true($restricted->isPrivate('set'));
	Assert::null($restricted->getVisibility());
	Assert::same('private', $restricted->getVisibility('set'));
});


test('property with public getter and protected setter using individual methods', function () {
	$class = new ClassType('Demo');
	$mixed = $class->addProperty('third')
		->setType('string')
		->setPublic()
		->setProtected('set');
	Assert::true($mixed->isPublic());
	Assert::false($mixed->isPublic('set'));
	Assert::true($mixed->isProtected('set'));
	Assert::same('public', $mixed->getVisibility());
	Assert::same('protected', $mixed->getVisibility('set'));
});


test('property with protected getter and private setter', function () {
	$class = new ClassType('Demo');
	$nested = $class->addProperty('fourth')
		->setType('string')
		->setProtected()
		->setPrivate('set');
	Assert::false($nested->isPublic());
	Assert::true($nested->isProtected());
	Assert::true($nested->isPrivate('set'));
	Assert::same('protected', $nested->getVisibility());
	Assert::same('private', $nested->getVisibility('set'));
});


testException('setVisibility throws exception for invalid getter visibility', function () {
	$class = new ClassType('Demo');
	$property = $class->addProperty('first')
		->setType('string');
	$property->setVisibility('invalid', 'public');
}, ValueError::class);


testException('setVisibility throws exception for invalid setter visibility', function () {
	$class = new ClassType('Demo');
	$property = $class->addProperty('first')
		->setType('string');
	$property->setVisibility('public', 'invalid');
}, ValueError::class);


test('asymmetric visibility renders correctly in class output', function () {
	$class = new ClassType('Demo');
	$class->addProperty('first')
		->setType('string');
	$class->addProperty('second')
		->setType('string')
		->setVisibility(null, 'private');
	$class->addProperty('third')
		->setType('string')
		->setPublic()
		->setProtected('set');
	$class->addProperty('fourth')
		->setType('string')
		->setProtected()
		->setPrivate('set');

	same(<<<'XX'
		class Demo
		{
			public string $first;
			private(set) string $second;
			public protected(set) string $third;
			protected private(set) string $fourth;
		}

		XX, (string) $class);
});
