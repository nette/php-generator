<?php

declare(strict_types=1);

use Nette\PhpGenerator\PromotedParameter;
use Nette\PhpGenerator\PropertyAccessMode;
use Nette\PhpGenerator\PropertyHookType;
use Nette\PhpGenerator\Visibility;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


testException('validate() throws exception for readOnly without type', function () {
	$param = (new PromotedParameter('foo'))
		->setReadOnly();

	$param->validate();
}, Nette\InvalidStateException::class, 'Property $foo: Read-only properties are only supported on typed property.');


test('validate() passes for readOnly with type', function () {
	$param = (new PromotedParameter('foo'))
		->setType('string')
		->setReadOnly();

	$param->validate();
	Assert::true(true); // no exception
});


test('visibility with PropertyAccessMode for asymmetric visibility', function () {
	$param = new PromotedParameter('foo');

	// Default visibility is null (which means public in PHP context)
	Assert::null($param->getVisibility());
	Assert::null($param->getVisibility(PropertyAccessMode::Set));

	// Set asymmetric visibility
	$param->setVisibility(Visibility::Public, Visibility::Private);

	Assert::same('public', $param->getVisibility(PropertyAccessMode::Get));
	Assert::same('private', $param->getVisibility(PropertyAccessMode::Set));
});


test('setPublic(), setProtected(), setPrivate() with PropertyAccessMode', function () {
	$param = new PromotedParameter('foo');

	$param->setPrivate(PropertyAccessMode::Set);
	Assert::true($param->isPrivate(PropertyAccessMode::Set));
	Assert::true($param->isPublic(PropertyAccessMode::Get));

	$param->setProtected(PropertyAccessMode::Get);
	Assert::true($param->isProtected(PropertyAccessMode::Get));
	Assert::true($param->isPrivate(PropertyAccessMode::Set));

	$param->setPublic(PropertyAccessMode::Set);
	Assert::true($param->isProtected(PropertyAccessMode::Get));
	Assert::true($param->isPublic(PropertyAccessMode::Set));
});


test('property hooks on promoted parameter', function () {
	$param = new PromotedParameter('foo');

	Assert::false($param->hasHook('get'));
	Assert::false($param->hasHook(PropertyHookType::Set));

	$getHook = $param->addHook('get', 'strtoupper($this->foo)');
	Assert::true($param->hasHook('get'));
	Assert::same($getHook, $param->getHook(PropertyHookType::Get));

	$setHook = $param->addHook(PropertyHookType::Set);
	$setHook->setBody('$this->foo = strtolower($value);', short: false);
	Assert::true($param->hasHook('set'));

	$hooks = $param->getHooks();
	Assert::count(2, $hooks);
	Assert::same($getHook, $hooks['get']);
	Assert::same($setHook, $hooks['set']);
});


test('__clone() clones hooks', function () {
	$param = new PromotedParameter('foo');
	$param->addHook('get', 'return 123');
	$param->addHook('set', '$this->foo = $value');

	$clone = clone $param;

	Assert::notSame($param->getHook('get'), $clone->getHook('get'));
	Assert::notSame($param->getHook('set'), $clone->getHook('set'));
});
