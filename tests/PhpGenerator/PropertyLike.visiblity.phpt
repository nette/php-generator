<?php declare(strict_types=1);

/**
 * Test: PropertyLike visibility
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('property has default public visibility', function () {
	$class = new ClassType('Demo');
	$default = $class->addProperty('first')
		->setType('string');
	Assert::true($default->isPublic());
	Assert::false($default->isProtected());
	Assert::false($default->isPrivate());
	Assert::null($default->getVisibility());
});


test('property with explicit public visibility', function () {
	$class = new ClassType('Demo');
	$public = $class->addProperty('second')
		->setType('string')
		->setPublic();
	Assert::true($public->isPublic());
	Assert::false($public->isProtected());
	Assert::false($public->isPrivate());
	Assert::same('public', $public->getVisibility());
});


test('property with protected visibility', function () {
	$class = new ClassType('Demo');
	$protected = $class->addProperty('third')
		->setType('string')
		->setProtected();
	Assert::false($protected->isPublic());
	Assert::true($protected->isProtected());
	Assert::false($protected->isPrivate());
	Assert::same('protected', $protected->getVisibility());
});


test('property with private visibility', function () {
	$class = new ClassType('Demo');
	$private = $class->addProperty('fourth')
		->setType('string')
		->setPrivate();
	Assert::false($private->isPublic());
	Assert::false($private->isProtected());
	Assert::true($private->isPrivate());
	Assert::same('private', $private->getVisibility());
});


test('property visibility can be changed', function () {
	$class = new ClassType('Demo');
	$changing = $class->addProperty('fifth')
		->setType('string')
		->setPublic();
	$changing->setVisibility('protected');
	Assert::false($changing->isPublic());
	Assert::true($changing->isProtected());
	Assert::false($changing->isPrivate());
});


testException('setVisibility throws exception for invalid visibility', function () {
	$class = new ClassType('Demo');
	$property = $class->addProperty('first')
		->setType('string');
	$property->setVisibility('invalid');
}, ValueError::class);


test('property visibility renders correctly in class output', function () {
	$class = new ClassType('Demo');
	$class->addProperty('first')
		->setType('string');
	$class->addProperty('second')
		->setType('string')
		->setPublic();
	$class->addProperty('third')
		->setType('string')
		->setProtected();
	$class->addProperty('fourth')
		->setType('string')
		->setPrivate();
	$changing = $class->addProperty('fifth')
		->setType('string')
		->setPublic();
	$changing->setVisibility('protected');

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
});
