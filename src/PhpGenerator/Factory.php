<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Reflection;
use function array_diff, array_filter, array_key_exists, array_map, constant, count, explode, file_get_contents, implode, is_object, is_subclass_of, method_exists, reset;
use const PHP_VERSION_ID;


/**
 * Creates a representations based on reflection or source code.
 */
final class Factory
{
	/** @var string[][]  */
	private array $bodyCache = [];

	/** @var Extractor[]  */
	private array $extractorCache = [];


	/** @param  \ReflectionClass<object>  $from */
	public function fromClassReflection(
		\ReflectionClass $from,
		bool $withBodies = false,
	): ClassLike
	{
		if ($withBodies && ($from->isAnonymous() || $from->isInternal() || $from->isInterface())) {
			throw new Nette\NotSupportedException('The $withBodies parameter cannot be used for anonymous or internal classes or interfaces.');
		}

		$enumIface = null;
		if (PHP_VERSION_ID >= 80100 && $from->isEnum()) {
			$class = new EnumType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
			$from = new \ReflectionEnum($from->getName());
			$enumIface = $from->isBacked() ? \BackedEnum::class : \UnitEnum::class;
		} elseif ($from->isAnonymous()) {
			$class = new ClassType;
		} elseif ($from->isInterface()) {
			$class = new InterfaceType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
		} elseif ($from->isTrait()) {
			$class = new TraitType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
		} else {
			$class = new ClassType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
			$class->setFinal($from->isFinal() && $class->isClass());
			$class->setAbstract($from->isAbstract() && $class->isClass());
			$class->setReadOnly(PHP_VERSION_ID >= 80200 && $from->isReadOnly());
		}

		$ifaces = $from->getInterfaceNames();
		foreach ($ifaces as $iface) {
			$ifaces = array_filter($ifaces, fn(string $item): bool => !is_subclass_of($iface, $item));
		}

		if ($from->isInterface()) {
			$class->setExtends($ifaces);
		} elseif ($ifaces) {
			$ifaces = array_diff($ifaces, [$enumIface]);
			$class->setImplements($ifaces);
		}

		$class->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		$class->setAttributes($this->getAttributes($from));
		if ($from->getParentClass()) {
			$class->setExtends($from->getParentClass()->name);
			$class->setImplements(array_diff($class->getImplements(), $from->getParentClass()->getInterfaceNames()));
		}

		$props = [];
		foreach ($from->getProperties() as $prop) {
			$declaringClass = Reflection::getPropertyDeclaringClass($prop);

			if ($prop->isDefault()
				&& $declaringClass->name === $from->name
				&& !$prop->isPromoted()
				&& !$class->isEnum()
			) {
				$props[] = $p = $this->fromPropertyReflection($prop);
				if ($withBodies) {
					$hookBodies ??= $this->getExtractor($declaringClass->getFileName())->extractPropertyHookBodies($declaringClass->name);
					foreach ($hookBodies[$prop->getName()] ?? [] as $hookType => [$body, $short]) {
						$p->getHook($hookType)->setBody($body, short: $short);
					}
				}
			}
		}

		if ($props) {
			$class->setProperties($props);
		}

		$methods = $resolutions = [];
		foreach ($from->getMethods() as $method) {
			$declaringMethod = Reflection::getMethodDeclaringMethod($method);
			$declaringClass = $declaringMethod->getDeclaringClass();

			if (
				$declaringClass->name === $from->name
				&& (!$enumIface || !method_exists($enumIface, $method->name))
			) {
				$methods[] = $m = $this->fromMethodReflection($method);
				if ($withBodies) {
					$bodies = &$this->bodyCache[$declaringClass->name];
					$bodies ??= $this->getExtractor($declaringClass->getFileName())->extractMethodBodies($declaringClass->name);
					if (isset($bodies[$declaringMethod->name])) {
						$m->setBody($bodies[$declaringMethod->name]);
					}
				}
			}

			$modifier = $declaringMethod->getModifiers() !== $method->getModifiers()
				? ' ' . $this->getVisibility($method)
				: null;
			$alias = $declaringMethod->name !== $method->name ? ' ' . $method->name : '';
			if ($modifier || $alias) {
				$resolutions[] = $declaringMethod->name . ' as' . $modifier . $alias;
			}
		}

		$class->setMethods($methods);

		foreach ($from->getTraitNames() as $trait) {
			$class->addTrait($trait, $resolutions);
			$resolutions = [];
		}

		$consts = $cases = [];
		foreach ($from->getReflectionConstants() as $const) {
			if ($class->isEnum() && $from->hasCase($const->name)) {
				$cases[] = $this->fromCaseReflection($const);
			} elseif ($const->getDeclaringClass()->name === $from->name) {
				$consts[] = $this->fromConstantReflection($const);
			}
		}

		if ($consts) {
			$class->setConstants($consts);
		}
		if ($cases) {
			$class->setCases($cases);
		}

		return $class;
	}


	public function fromMethodReflection(\ReflectionMethod $from): Method
	{
		$method = new Method($from->name);
		$method->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$method->setStatic($from->isStatic());
		$isInterface = $from->getDeclaringClass()->isInterface();
		$method->setVisibility($isInterface ? null : $this->getVisibility($from));
		$method->setFinal($from->isFinal());
		$method->setAbstract($from->isAbstract() && !$isInterface);
		$method->setReturnReference($from->returnsReference());
		$method->setVariadic($from->isVariadic());
		$method->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		$method->setAttributes($this->getAttributes($from));
		$method->setReturnType((string) $from->getReturnType());

		return $method;
	}


	public function fromFunctionReflection(\ReflectionFunction $from, bool $withBody = false): GlobalFunction|Closure
	{
		$function = $from->isClosure() ? new Closure : new GlobalFunction($from->name);
		$function->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$function->setReturnReference($from->returnsReference());
		$function->setVariadic($from->isVariadic());
		if (!$from->isClosure()) {
			$function->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		}

		$function->setAttributes($this->getAttributes($from));
		$function->setReturnType((string) $from->getReturnType());

		if ($withBody) {
			if ($from->isClosure() || $from->isInternal()) {
				throw new Nette\NotSupportedException('The $withBody parameter cannot be used for closures or internal functions.');
			}

			$function->setBody($this->getExtractor($from->getFileName())->extractFunctionBody($from->name));
		}

		return $function;
	}


	public function fromCallable(callable $from): Method|GlobalFunction|Closure
	{
		$ref = Nette\Utils\Callback::toReflection($from);
		return $ref instanceof \ReflectionMethod
			? $this->fromMethodReflection($ref)
			: $this->fromFunctionReflection($ref);
	}


	public function fromParameterReflection(\ReflectionParameter $from): Parameter
	{
		if ($from->isPromoted()) {
			$property = $from->getDeclaringClass()->getProperty($from->name);
			$param = (new PromotedParameter($from->name))
				->setVisibility($this->getVisibility($property))
				->setReadOnly(PHP_VERSION_ID >= 80100 && $property->isReadonly());
			$this->addHooks($property, $param);
		} else {
			$param = new Parameter($from->name);
		}
		$param->setReference($from->isPassedByReference());
		$param->setType((string) $from->getType());

		if ($from->isDefaultValueAvailable()) {
			if ($from->isDefaultValueConstant()) {
				$parts = explode('::', $from->getDefaultValueConstantName());
				if (count($parts) > 1) {
					$parts[0] = Helpers::tagName($parts[0]);
				}

				$param->setDefaultValue(new Literal(implode('::', $parts)));
			} elseif (is_object($from->getDefaultValue())) {
				$param->setDefaultValue($this->fromObject($from->getDefaultValue()));
			} else {
				$param->setDefaultValue($from->getDefaultValue());
			}
		}

		$param->setAttributes($this->getAttributes($from));
		return $param;
	}


	public function fromConstantReflection(\ReflectionClassConstant $from): Constant
	{
		$const = new Constant($from->name);
		$const->setValue($from->getValue());
		$const->setVisibility($this->getVisibility($from));
		$const->setFinal(PHP_VERSION_ID >= 80100 && $from->isFinal());
		$const->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		$const->setAttributes($this->getAttributes($from));
		return $const;
	}


	public function fromCaseReflection(\ReflectionClassConstant $from): EnumCase
	{
		$const = new EnumCase($from->name);
		$const->setValue($from->getValue()->value ?? null);
		$const->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		$const->setAttributes($this->getAttributes($from));
		return $const;
	}


	public function fromPropertyReflection(\ReflectionProperty $from): Property
	{
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop = new Property($from->name);
		$prop->setValue($defaults[$prop->getName()] ?? null);
		$prop->setStatic($from->isStatic());
		$prop->setVisibility($this->getVisibility($from));
		$prop->setType((string) $from->getType());
		$prop->setInitialized($from->hasType() && array_key_exists($prop->getName(), $defaults));
		$prop->setReadOnly(PHP_VERSION_ID >= 80100 && $from->isReadOnly());
		$prop->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		$prop->setAttributes($this->getAttributes($from));

		if (PHP_VERSION_ID >= 80400) {
			$this->addHooks($from, $prop);
			$isInterface = $from->getDeclaringClass()->isInterface();
			$prop->setFinal($from->isFinal() && !$prop->isPrivate(PropertyAccessMode::Set));
			$prop->setAbstract($from->isAbstract() && !$isInterface);
		}
		return $prop;
	}


	private function addHooks(\ReflectionProperty $from, Property|PromotedParameter $prop): void
	{
		if (PHP_VERSION_ID < 80400) {
			return;
		}

		$getV = $this->getVisibility($from);
		$setV = $from->isPrivateSet()
			? Visibility::Private
			: ($from->isProtectedSet() ? Visibility::Protected : $getV);
		$defaultSetV = $from->isReadOnly() && $getV !== Visibility::Private
			? Visibility::Protected
			: $getV;
		if ($setV !== $defaultSetV) {
			$prop->setVisibility($getV === Visibility::Public ? null : $getV, $setV);
		}

		foreach ($from->getHooks() as $type => $hook) {
			$params = $hook->getParameters();
			if (
				count($params) === 1
				&& $params[0]->getName() === 'value'
				&& $params[0]->getType() == $from->getType() // intentionally ==
			) {
				$params = [];
			}
			$prop->addHook($type)
				->setParameters(array_map([$this, 'fromParameterReflection'], $params))
				->setAbstract($hook->isAbstract())
				->setFinal($hook->isFinal())
				->setReturnReference($hook->returnsReference())
				->setComment(Helpers::unformatDocComment((string) $hook->getDocComment()))
				->setAttributes($this->getAttributes($hook));
		}
	}


	public function fromObject(object $obj): Literal
	{
		return new Literal('new \\' . $obj::class . '(/* unknown */)');
	}


	public function fromClassCode(string $code): ClassLike
	{
		$classes = $this->fromCode($code)->getClasses();
		return reset($classes) ?: throw new Nette\InvalidStateException('The code does not contain any class.');
	}


	public function fromCode(string $code): PhpFile
	{
		$reader = new Extractor($code);
		return $reader->extractAll();
	}


	private function getAttributes($from): array
	{
		return array_map(function ($attr) {
			$args = $attr->getArguments();
			foreach ($args as &$arg) {
				if (is_object($arg)) {
					$arg = $this->fromObject($arg);
				}
			}

			return new Attribute($attr->getName(), $args);
		}, $from->getAttributes());
	}


	private function getVisibility($from): string
	{
		return $from->isPrivate()
			? Visibility::Private
			: ($from->isProtected() ? Visibility::Protected : Visibility::Public);
	}


	private function getExtractor(string $file): Extractor
	{
		$cache = &$this->extractorCache[$file];
		$cache ??= new Extractor(file_get_contents($file));
		return $cache;
	}
}
