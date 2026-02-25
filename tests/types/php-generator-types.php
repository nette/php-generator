<?php declare(strict_types=1);

/**
 * PHPStan type tests.
 */

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Closure;
use Nette\PhpGenerator\Constant;
use Nette\PhpGenerator\EnumCase;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use Nette\PhpGenerator\PropertyHook;
use Nette\PhpGenerator\TraitType;
use Nette\PhpGenerator\TraitUse;
use Nette\Utils\Type;
use function PHPStan\Testing\assertType;


function testParameterGetType(Parameter $param): void
{
	assertType('string|null', $param->getType());
	assertType('string|null', $param->getType(false));
	assertType(Type::class . '|null', $param->getType(true));
}


function testPropertyGetType(Property $prop): void
{
	assertType('string|null', $prop->getType());
	assertType('string|null', $prop->getType(false));
	assertType(Type::class . '|null', $prop->getType(true));
}


function testMethodGetReturnType(Method $method): void
{
	assertType('string|null', $method->getReturnType());
	assertType('string|null', $method->getReturnType(false));
	assertType(Type::class . '|null', $method->getReturnType(true));
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
	assertType('array<string, ' . ClassType::class . '|' . EnumType::class . '|' . InterfaceType::class . '|' . TraitType::class . '>', $file->getClasses());
	assertType('array<string, ' . GlobalFunction::class . '>', $file->getFunctions());
}


function testPhpNamespaceCollections(PhpNamespace $ns): void
{
	assertType('array<string, ' . ClassType::class . '|' . EnumType::class . '|' . InterfaceType::class . '|' . TraitType::class . '>', $ns->getClasses());
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
