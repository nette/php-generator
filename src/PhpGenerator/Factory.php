<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use PhpParser;
use PhpParser\Node;
use PhpParser\ParserFactory;
use function strlen;
use function substr_replace;


/**
 * Creates a representation based on reflection.
 */
final class Factory
{
	use Nette\SmartObject;

	public function fromClassReflection(\ReflectionClass $from, bool $withBodies = false): ClassType
	{
		$class = $from->isAnonymous()
			? new ClassType
			: new ClassType($from->getShortName(), new PhpNamespace($from->getNamespaceName()));
		$class->setType($from->isInterface() ? $class::TYPE_INTERFACE : ($from->isTrait() ? $class::TYPE_TRAIT : $class::TYPE_CLASS));
		$class->setFinal($from->isFinal() && $class->isClass());
		$class->setAbstract($from->isAbstract() && $class->isClass());

		$ifaces = $from->getInterfaceNames();
		foreach ($ifaces as $iface) {
			$ifaces = array_filter($ifaces, function (string $item) use ($iface): bool {
				return !is_subclass_of($iface, $item);
			});
		}
		$class->setImplements($ifaces);

		$class->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		if ($from->getParentClass()) {
			$class->setExtends($from->getParentClass()->name);
			$class->setImplements(array_diff($class->getImplements(), $from->getParentClass()->getInterfaceNames()));
		}
		$props = $methods = $consts = [];
		foreach ($from->getProperties() as $prop) {
			if ($prop->isDefault() && $prop->getDeclaringClass()->name === $from->name) {
				$props[] = $this->fromPropertyReflection($prop);
			}
		}
		$class->setProperties($props);

		$bodies = [];
		foreach ($from->getMethods() as $method) {
			if ($method->getDeclaringClass()->name === $from->name) {
				$methods[] = $m = $this->fromMethodReflection($method);
				if ($withBodies) {
					$srcMethod = Nette\Utils\Reflection::getMethodDeclaringMethod($method);
					$srcClass = $srcMethod->getDeclaringClass()->name;
					$b = $bodies[$srcClass] = $bodies[$srcClass] ?? $this->loadMethodBodies($srcMethod->getDeclaringClass());
					if (isset($b[$srcMethod->name])) {
						$m->setBody($b[$srcMethod->name]);
					}
				}
			}
		}
		$class->setMethods($methods);

		foreach ($from->getReflectionConstants() as $const) {
			if ($const->getDeclaringClass()->name === $from->name) {
				$consts[] = $this->fromConstantReflection($const);
			}
		}
		$class->setConstants($consts);

		return $class;
	}


	public function fromMethodReflection(\ReflectionMethod $from): Method
	{
		$method = new Method($from->name);
		$method->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$method->setStatic($from->isStatic());
		$isInterface = $from->getDeclaringClass()->isInterface();
		$method->setVisibility($from->isPrivate()
			? ClassType::VISIBILITY_PRIVATE
			: ($from->isProtected() ? ClassType::VISIBILITY_PROTECTED : ($isInterface ? null : ClassType::VISIBILITY_PUBLIC))
		);
		$method->setFinal($from->isFinal());
		$method->setAbstract($from->isAbstract() && !$isInterface);
		$method->setBody($from->isAbstract() ? null : '');
		$method->setReturnReference($from->returnsReference());
		$method->setVariadic($from->isVariadic());
		$method->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		if ($from->getReturnType() instanceof \ReflectionNamedType) {
			$method->setReturnType($from->getReturnType()->getName());
			$method->setReturnNullable($from->getReturnType()->allowsNull());
		}
		return $method;
	}


	/** @return GlobalFunction|Closure */
	public function fromFunctionReflection(\ReflectionFunction $from, bool $withBody = false)
	{
		$function = $from->isClosure() ? new Closure : new GlobalFunction($from->name);
		$function->setParameters(array_map([$this, 'fromParameterReflection'], $from->getParameters()));
		$function->setReturnReference($from->returnsReference());
		$function->setVariadic($from->isVariadic());
		if (!$from->isClosure()) {
			$function->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		}
		if ($from->getReturnType() instanceof \ReflectionNamedType) {
			$function->setReturnType($from->getReturnType()->getName());
			$function->setReturnNullable($from->getReturnType()->allowsNull());
		}
		$function->setBody($withBody ? $this->loadFunctionBody($from) : '');
		return $function;
	}


	/** @return Method|GlobalFunction|Closure */
	public function fromCallable(callable $from)
	{
		$ref = Nette\Utils\Callback::toReflection($from);
		return $ref instanceof \ReflectionMethod
			? self::fromMethodReflection($ref)
			: self::fromFunctionReflection($ref);
	}


	public function fromParameterReflection(\ReflectionParameter $from): Parameter
	{
		$param = new Parameter($from->name);
		$param->setReference($from->isPassedByReference());
		$param->setType($from->getType() instanceof \ReflectionNamedType ? $from->getType()->getName() : null);
		$param->setNullable($from->hasType() && $from->getType()->allowsNull());
		if ($from->isDefaultValueAvailable()) {
			$param->setDefaultValue($from->isDefaultValueConstant()
				? new Literal($from->getDefaultValueConstantName())
				: $from->getDefaultValue());
			$param->setNullable($param->isNullable() && $param->getDefaultValue() !== null);
		}
		return $param;
	}


	public function fromConstantReflection(\ReflectionClassConstant $from): Constant
	{
		$const = new Constant($from->name);
		$const->setValue($from->getValue());
		$const->setVisibility($from->isPrivate()
			? ClassType::VISIBILITY_PRIVATE
			: ($from->isProtected() ? ClassType::VISIBILITY_PROTECTED : ClassType::VISIBILITY_PUBLIC)
		);
		$const->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		return $const;
	}


	public function fromPropertyReflection(\ReflectionProperty $from): Property
	{
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop = new Property($from->name);
		$prop->setValue($defaults[$prop->getName()] ?? null);
		$prop->setStatic($from->isStatic());
		$prop->setVisibility($from->isPrivate()
			? ClassType::VISIBILITY_PRIVATE
			: ($from->isProtected() ? ClassType::VISIBILITY_PROTECTED : ClassType::VISIBILITY_PUBLIC)
		);
		if (PHP_VERSION_ID >= 70400 && ($from->getType() instanceof \ReflectionNamedType)) {
			$prop->setType($from->getType()->getName());
			$prop->setNullable($from->getType()->allowsNull());
			$prop->setInitialized(array_key_exists($prop->getName(), $defaults));
		}
		$prop->setComment(Helpers::unformatDocComment((string) $from->getDocComment()));
		return $prop;
	}


	private function loadMethodBodies(\ReflectionClass $from): array
	{
		if ($from->isAnonymous()) {
			throw new Nette\NotSupportedException('Anonymous classes are not supported.');
		}

		[$code, $stmts] = $this->parse($from);
		$nodeFinder = new PhpParser\NodeFinder;
		$class = $nodeFinder->findFirst($stmts, function (Node $node) use ($from) {
			return ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Trait_) && $node->namespacedName->toString() === $from->name;
		});

		$bodies = [];
		foreach ($nodeFinder->findInstanceOf($class, Node\Stmt\ClassMethod::class) as $method) {
			/** @var Node\Stmt\ClassMethod $method */
			if ($method->stmts) {
				$body = $this->extractBodyWithFQN($nodeFinder, $code, $method->stmts);
				$bodies[$method->name->toString()] = Helpers::indentPhp($body, -2);
			}
		}
		return $bodies;
	}


	private function loadFunctionBody(\ReflectionFunction $from): string
	{
		if ($from->isClosure()) {
			throw new Nette\NotSupportedException('Closures are not supported.');
		}

		[$code, $stmts] = $this->parse($from);

		$nodeFinder = new PhpParser\NodeFinder;
		/** @var Node\Stmt\Function_ $function */
		$function = $nodeFinder->findFirst($stmts, function (Node $node) use ($from) {
			return $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $from->name;
		});

		$body = $this->extractBodyWithFQN($nodeFinder, $code, $function->stmts);
		return Helpers::indentPhp($body, -1);
	}

	/**
	 * @param PhpParser\NodeFinder $nodeFinder
	 * @param string $originalCode
	 * @param Node[] $statements
	 * @return string
	 */
	private function extractBodyWithFQN(PhpParser\NodeFinder $nodeFinder, string $originalCode, array $statements): string
	{
		$resolvedFQNames = [];
		//collect all name-nodes with resolved fully-qualified name
		foreach ($nodeFinder->findInstanceOf($statements, Node\Name::class) as $namedNode) {
			if ($namedNode->hasAttribute('resolvedName')
				&& $namedNode->getAttribute('resolvedName') instanceof Node\Name\FullyQualified) {
				$resolvedFQNames[] = $namedNode->getAttributes();
			}
		}

		$start = $statements[0]->getAttribute('startFilePos');
		$body = substr($originalCode, $start, end($statements)->getAttribute('endFilePos') - $start + 1);

		//if there are some resolved names in original code then replace them with fqn
		if ($resolvedFQNames !== []) {
			//sort collected resolved names by position in file
			usort($resolvedFQNames, function ($a, $b) {
				return $a['startFilePos'] <=> $b['startFilePos'];
			});
			$correctiveOffset = -$start;
			//replace changes body length so we need correct offset
			foreach ($resolvedFQNames as $resolvedFQName) {
				$replacement = $resolvedFQName['resolvedName']->toCodeString();
				$replacingStringLength = $resolvedFQName['endFilePos'] - $resolvedFQName['startFilePos'] + 1;
				$body = substr_replace(
					$body,
					$replacement,
					$correctiveOffset + $resolvedFQName['startFilePos'],
					$replacingStringLength
				);
				$correctiveOffset += strlen($replacement) - $replacingStringLength;
			}
		}
		return $body;
	}

	private function parse($from): array
	{
		$file = $from->getFileName();
		if (!class_exists(ParserFactory::class)) {
			throw new Nette\NotSupportedException("PHP-Parser is required to load method bodies, install package 'nikic/php-parser'.");
		} elseif (!$file) {
			throw new Nette\InvalidStateException("Source code of $from->name not found.");
		}

		$lexer = new PhpParser\Lexer(['usedAttributes' => ['startFilePos', 'endFilePos']]);
		$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, $lexer);
		$code = file_get_contents($file);
		$code = str_replace("\r\n", "\n", $code);
		$stmts = $parser->parse($code);

		$traverser = new PhpParser\NodeTraverser;
		//set 'replaceNodes' option to false to mark resolved names instead of replace
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes' => false]));
		$stmts = $traverser->traverse($stmts);

		return [$code, $stmts];
	}
}
