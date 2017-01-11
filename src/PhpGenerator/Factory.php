<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Creates a representation based on reflection.
 */
class Factory
{
	use Nette\SmartObject;


	public function fromClassReflection(\ReflectionClass $from)
	{
		if (PHP_VERSION_ID >= 70000 && $from->isAnonymous()) {
			$class = new ClassType;
		} else {
			$class = new ClassType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
		}
		$class->setType($from->isInterface() ? 'interface' : ($from->isTrait() ? 'trait' : 'class'));
		$class->setFinal($from->isFinal() && $class->getType() === 'class');
		$class->setAbstract($from->isAbstract() && $class->getType() === 'class');
		$class->setImplements($from->getInterfaceNames());
		$class->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		if ($from->getParentClass()) {
			$class->setExtends($from->getParentClass()->getName());
			$class->setImplements(array_diff($class->getImplements(), $from->getParentClass()->getInterfaceNames()));
		}
		$props = $methods = [];
		foreach ($from->getProperties() as $prop) {
			if ($prop->isDefault() && $prop->getDeclaringClass()->getName() === $from->getName()) {
				$props[$prop->getName()] = $this->fromPropertyReflection($prop);
			}
		}
		$class->setProperties($props);
		foreach ($from->getMethods() as $method) {
			if ($method->getDeclaringClass()->getName() === $from->getName()) {
				$methods[$method->getName()] = $this->fromFunctionReflection($method)->setNamespace($class->getNamespace());
			}
		}
		$class->setMethods($methods);
		return $class;
	}


	public function fromFunctionReflection(\ReflectionFunctionAbstract $from)
	{
		$method = new Method($from->isClosure() ? NULL : $from->getName());
		$params = [];
		foreach ($from->getParameters() as $param) {
			$params[$param->getName()] = $this->fromParameterReflection($param);
		}
		$method->setParameters($params);
		if ($from instanceof \ReflectionMethod) {
			$isInterface = $from->getDeclaringClass()->isInterface();
			$method->setStatic($from->isStatic());
			$method->setVisibility($from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : ($isInterface ? NULL : 'public')));
			$method->setFinal($from->isFinal());
			$method->setAbstract($from->isAbstract() && !$isInterface);
			$method->setBody($from->isAbstract() ? FALSE : '');
		}
		$method->setReturnReference($from->returnsReference());
		$method->setVariadic($from->isVariadic());
		$method->setComment(Helpers::unformatDocComment($from->getDocComment()));
		if (PHP_VERSION_ID >= 70000 && $from->hasReturnType()) {
			$method->setReturnType((string) $from->getReturnType());
			$method->setReturnNullable($from->getReturnType()->allowsNull());
		}
		return $method;
	}


	public function fromParameterReflection(\ReflectionParameter $from)
	{
		$param = new Parameter($from->getName());
		$param->setReference($from->isPassedByReference());
		if (PHP_VERSION_ID >= 70000) {
			$param->setTypeHint($from->hasType() ? (string) $from->getType() : NULL);
			$param->setNullable($from->hasType() && $from->getType()->allowsNull());
		} elseif ($from->isArray() || $from->isCallable()) {
			$param->setTypeHint($from->isArray() ? 'array' : 'callable');
		} else {
			try {
				$param->setTypeHint($from->getClass() ? $from->getClass()->getName() : NULL);
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					$param->setTypeHint($m[1]);
				} else {
					throw $e;
				}
			}
		}
		if ($from->isDefaultValueAvailable()) {
			$param->setOptional(TRUE);
			$param->setDefaultValue($from->isDefaultValueConstant()
				? new PhpLiteral($from->getDefaultValueConstantName())
				: $from->getDefaultValue());
			$param->setNullable($param->isNullable() && $param->getDefaultValue() !== NULL);
		}
		return $param;
	}


	public function fromPropertyReflection(\ReflectionProperty $from)
	{
		$prop = new Property($from->getName());
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->setValue(isset($defaults[$prop->getName()]) ? $defaults[$prop->getName()] : NULL);
		$prop->setStatic($from->isStatic());
		$prop->setVisibility($from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public'));
		$prop->setComment(Helpers::unformatDocComment($from->getDocComment()));
		return $prop;
	}

}
