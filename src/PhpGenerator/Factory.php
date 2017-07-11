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

	/**
	 * @return ClassType
	 */
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
				$props[] = $this->fromPropertyReflection($prop);
			}
		}
		$class->setProperties($props);
		foreach ($from->getMethods() as $method) {
			if ($method->getDeclaringClass()->getName() === $from->getName()) {
				$methods[] = $this->fromMethodReflection($method)->setNamespace($class->getNamespace());
			}
		}
		$class->setMethods($methods);
		return $class;
	}


	/**
	 * @return Method
	 */
	public function fromMethodReflection(\ReflectionMethod $from)
	{
		$method = new Method($from->getName());
		$method->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$method->setStatic($from->isStatic());
		$isInterface = $from->getDeclaringClass()->isInterface();
		$method->setVisibility($from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : ($isInterface ? null : 'public')));
		$method->setFinal($from->isFinal());
		$method->setAbstract($from->isAbstract() && !$isInterface);
		$method->setBody($from->isAbstract() ? false : '');
		$method->setReturnReference($from->returnsReference());
		$method->setVariadic($from->isVariadic());
		$method->setComment(Helpers::unformatDocComment($from->getDocComment()));
		if (PHP_VERSION_ID >= 70000 && $from->hasReturnType()) {
			$method->setReturnType((string) $from->getReturnType());
			$method->setReturnNullable($from->getReturnType()->allowsNull());
		}
		return $method;
	}


	/**
	 * @return GlobalFunction|Closure
	 */
	public function fromFunctionReflection(\ReflectionFunction $from)
	{
		$function = $from->isClosure() ? new Closure : new GlobalFunction($from->getName());
		$function->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$function->setReturnReference($from->returnsReference());
		$function->setVariadic($from->isVariadic());
		if (!$from->isClosure()) {
			$function->setComment(Helpers::unformatDocComment($from->getDocComment()));
		}
		if (PHP_VERSION_ID >= 70000 && $from->hasReturnType()) {
			$function->setReturnType((string) $from->getReturnType());
			$function->setReturnNullable($from->getReturnType()->allowsNull());
		}
		return $function;
	}


	/**
	 * @return Parameter
	 */
	public function fromParameterReflection(\ReflectionParameter $from)
	{
		$param = new Parameter($from->getName());
		$param->setReference($from->isPassedByReference());
		if (PHP_VERSION_ID >= 70000) {
			$param->setTypeHint($from->hasType() ? (string) $from->getType() : null);
			$param->setNullable($from->hasType() && $from->getType()->allowsNull());
		} elseif ($from->isArray() || $from->isCallable()) {
			$param->setTypeHint($from->isArray() ? 'array' : 'callable');
		} else {
			try {
				$param->setTypeHint($from->getClass() ? $from->getClass()->getName() : null);
			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					$param->setTypeHint($m[1]);
				} else {
					throw $e;
				}
			}
		}
		if ($from->isDefaultValueAvailable()) {
			$param->setOptional(true);
			$param->setDefaultValue($from->isDefaultValueConstant()
				? new PhpLiteral($from->getDefaultValueConstantName())
				: $from->getDefaultValue());
			$param->setNullable($param->isNullable() && $param->getDefaultValue() !== null);
		}
		return $param;
	}


	/**
	 * @return Property
	 */
	public function fromPropertyReflection(\ReflectionProperty $from)
	{
		$prop = new Property($from->getName());
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->setValue(isset($defaults[$prop->getName()]) ? $defaults[$prop->getName()] : null);
		$prop->setStatic($from->isStatic());
		$prop->setVisibility($from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public'));
		$prop->setComment(Helpers::unformatDocComment($from->getDocComment()));
		return $prop;
	}
}
