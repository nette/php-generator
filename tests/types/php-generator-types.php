<?php declare(strict_types=1);

/**
 * PHPStan type tests.
 */

use Nette\PhpGenerator\{
	ClassType, Closure, Constant, EnumCase, EnumType, GlobalFunction, InterfaceType,
	Method, Parameter, PhpFile, PhpNamespace, Property, PropertyHook, TraitType, TraitUse,
};
use Nette\Utils\Type;
use function PHPStan\Testing\assertType;


function testParameterGetType(Parameter $param): void
{
	assertType(Type::class . '|null', $param->getType());
}


function testPropertyGetType(Property $prop): void
{
	assertType(Type::class . '|null', $prop->getType());
}


function testMethodGetReturnType(Method $method): void
{
	assertType(Type::class . '|null', $method->getReturnType());
}


function testConstantGetType(Constant $const): void
{
	assertType(Type::class . '|null', $const->getType());
}


function testEnumGetCases(EnumType $enum): void
{
	assertType('array<string, ' . EnumCase::class . '>', $enum->getCases());
}


function testClosureGetUses(Closure $closure): void
{
	assertType('list<' . Parameter::class . '>', $closure->getUses());
}


function testPropertyHookGetParameters(PropertyHook $hook): void
{
	assertType('array<string, ' . Parameter::class . '>', $hook->getParameters());
}


function testPropertyGetHooks(Property $prop): void
{
	assertType('array<string, ' . PropertyHook::class . '>', $prop->getHooks());
}


function testMethodGetVisibility(Method $method): void
{
	assertType("'private'|'protected'|'public'|null", $method->getVisibility());
}


function testPropertyGetVisibility(Property $prop): void
{
	assertType("'private'|'protected'|'public'|null", $prop->getVisibility());
}


function testClassTypeCollections(ClassType $class): void
{
	assertType('array<string, ' . Method::class . '>', $class->getMethods());
	assertType('array<string, ' . Property::class . '>', $class->getProperties());
	assertType('array<string, ' . Constant::class . '>', $class->getConstants());
	assertType('array<string, ' . TraitUse::class . '>', $class->getTraits());
}


function testPhpFileCollections(PhpFile $file): void
{
	assertType('array<string, ' . PhpNamespace::class . '>', $file->getNamespaces());
	assertType(
		'array<string, ' . ClassType::class . '|' . EnumType::class . '|' . InterfaceType::class . '|' . TraitType::class . '>',
		$file->getClasses(),
	);
	assertType('array<string, ' . GlobalFunction::class . '>', $file->getFunctions());
}


function testPhpNamespaceCollections(PhpNamespace $ns): void
{
	assertType(
		'array<string, ' . ClassType::class . '|' . EnumType::class . '|' . InterfaceType::class . '|' . TraitType::class . '>',
		$ns->getClasses(),
	);
	assertType('array<string, ' . GlobalFunction::class . '>', $ns->getFunctions());
	assertType('array<string, string>', $ns->getUses());
}


function testTraitUseGetResolutions(TraitUse $trait): void
{
	assertType('list<string>', $trait->getResolutions());
}


function testGlobalFunctionGetParameters(GlobalFunction $fn): void
{
	assertType('array<string, ' . Parameter::class . '>', $fn->getParameters());
}
