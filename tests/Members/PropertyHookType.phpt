<?php declare(strict_types=1);

use Nette\PhpGenerator\{Property, PropertyAccessMode, PropertyHookType};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::same(PropertyAccessMode::Set, PropertyHookType::Set);

$property = new Property('foo');
$hook = $property->addHook(PropertyHookType::Get);
Assert::same($hook, $property->getHook(PropertyAccessMode::Get));
