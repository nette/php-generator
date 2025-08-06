<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;
use function array_filter, array_map, count, end, get_debug_type, implode, is_scalar, ltrim, preg_replace, rtrim, str_contains, str_repeat, str_replace, strlen, substr;


/**
 * Generates PHP code.
 */
class Printer
{
	public int $wrapLength = 120;
	public string $indentation = "\t";
	public int $linesBetweenProperties = 0;
	public int $linesBetweenMethods = 2;
	public int $linesBetweenUseTypes = 0;
	public string $returnTypeColon = ': ';
	public bool $bracesOnNextLine = true;
	public bool $singleParameterOnOneLine = false;
	public bool $omitEmptyNamespaces = true;
	protected ?PhpNamespace $namespace = null;
	protected ?Dumper $dumper;
	private bool $resolveTypes = true;


	public function __construct()
	{
		$this->dumper = new Dumper;
	}


	public function printFunction(GlobalFunction $function, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$line = 'function '
			. ($function->getReturnReference() ? '&' : '')
			. $function->getName();
		$returnType = $this->printReturnType($function);
		$params = $this->printParameters($function, strlen($line) + strlen($returnType) + 2); // 2 = parentheses
		$body = $this->printFunctionBody($function);
		$braceOnNextLine = $this->isBraceOnNextLine(str_contains($params, "\n"), (bool) $returnType);

		return $this->printDocComment($function)
			. $this->printAttributes($function->getAttributes())
			. $line
			. $params
			. $returnType
			. ($braceOnNextLine ? "\n" : ' ')
			. "{\n" . $this->indent($body) . "}\n";
	}


	public function printClosure(Closure $closure, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$uses = [];
		foreach ($closure->getUses() as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}

		$useStr = strlen($tmp = implode(', ', $uses)) > $this->wrapLength && count($uses) > 1
			? "\n" . $this->indentation . implode(",\n" . $this->indentation, $uses) . ",\n"
			: $tmp;
		$body = $this->printFunctionBody($closure);

		return $this->printAttributes($closure->getAttributes(), inline: true)
			. 'function '
			. ($closure->getReturnReference() ? '&' : '')
			. $this->printParameters($closure)
			. ($uses ? " use ($useStr)" : '')
			. $this->printReturnType($closure)
			. " {\n" . $this->indent($body) . '}';
	}


	public function printArrowFunction(Closure $closure, ?PhpNamespace $namespace = null): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		foreach ($closure->getUses() as $use) {
			if ($use->isReference()) {
				throw new Nette\InvalidArgumentException('Arrow function cannot bind variables by-reference.');
			}
		}

		$body = $this->printFunctionBody($closure);

		return $this->printAttributes($closure->getAttributes())
			. 'fn'
			. ($closure->getReturnReference() ? '&' : '')
			. $this->printParameters($closure)
			. $this->printReturnType($closure)
			. ' => ' . rtrim($body, "\n") . ';';
	}


	public function printMethod(Method $method, ?PhpNamespace $namespace = null, bool $isInterface = false): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$method->validate();
		$line = ($method->isAbstract() && !$isInterface ? 'abstract ' : '')
			. ($method->isFinal() ? 'final ' : '')
			. ($method->getVisibility() ? $method->getVisibility() . ' ' : '')
			. ($method->isStatic() ? 'static ' : '')
			. 'function '
			. ($method->getReturnReference() ? '&' : '')
			. $method->getName();
		$returnType = $this->printReturnType($method);
		$params = $this->printParameters($method, strlen($line) + strlen($returnType) + strlen($this->indentation) + 2);
		$body = $this->printFunctionBody($method);
		$braceOnNextLine = $this->isBraceOnNextLine(str_contains($params, "\n"), (bool) $returnType);

		return $this->printDocComment($method)
			. $this->printAttributes($method->getAttributes())
			. $line
			. $params
			. $returnType
			. ($method->isAbstract() || $isInterface
				? ";\n"
				: ($braceOnNextLine ? "\n" : ' ') . "{\n" . $this->indent($body) . "}\n");
	}


	private function printFunctionBody(Closure|GlobalFunction|Method|PropertyHook $function): string
	{
		$code = Helpers::simplifyTaggedNames($function->getBody(), $this->namespace);
		$code = Strings::normalize($code);
		return ltrim(rtrim($code) . "\n");
	}


	public function printClass(
		ClassType|InterfaceType|TraitType|EnumType $class,
		?PhpNamespace $namespace = null,
	): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$class->validate();
		$resolver = $this->namespace
			? [$namespace, 'simplifyType']
			: fn($s) => $s;

		$traits = [];
		if ($class instanceof ClassType || $class instanceof TraitType || $class instanceof EnumType) {
			foreach ($class->getTraits() as $trait) {
				$resolutions = implode(";\n", $trait->getResolutions());
				$resolutions = Helpers::simplifyTaggedNames($resolutions, $this->namespace);
				$traits[] = $this->printDocComment($trait)
					. 'use ' . $resolver($trait->getName())
					. ($resolutions
						? " {\n" . $this->indent($resolutions) . ";\n}\n"
						: ";\n");
			}
		}

		$cases = [];
		$enumType = null;
		if ($class instanceof EnumType) {
			$enumType = $class->getType();
			foreach ($class->getCases() as $case) {
				$enumType ??= is_scalar($case->getValue()) ? get_debug_type($case->getValue()) : null;
				$cases[] = $this->printDocComment($case)
					. $this->printAttributes($case->getAttributes())
					. 'case ' . $case->getName()
					. ($case->getValue() === null ? '' : ' = ' . $this->dump($case->getValue()))
					. ";\n";
			}
		}

		$readOnlyClass = $class instanceof ClassType && $class->isReadOnly();
		$consts = [];
		$methods = [];
		if (
			$class instanceof ClassType
			|| $class instanceof InterfaceType
			|| $class instanceof TraitType
			|| $class instanceof EnumType
		) {
			foreach ($class->getConstants() as $const) {
				$consts[] = $this->printConstant($const);
			}

			foreach ($class->getMethods() as $method) {
				if ($readOnlyClass && $method->getName() === Method::Constructor) {
					$method = clone $method;
					array_map(fn($param) => $param instanceof PromotedParameter ? $param->setReadOnly(false) : null, $method->getParameters());
				}
				$methods[] = $this->printMethod($method, $namespace, $class->isInterface());
			}
		}

		$properties = [];
		if ($class instanceof ClassType || $class instanceof TraitType || $class instanceof InterfaceType) {
			foreach ($class->getProperties() as $property) {
				$properties[] = $this->printProperty($property, $readOnlyClass, $class instanceof InterfaceType);
			}
		}

		$members = array_filter([
			implode('', $traits),
			$this->joinProperties($consts),
			$this->joinProperties($cases),
			$this->joinProperties($properties),
			($methods && $properties ? str_repeat("\n", $this->linesBetweenMethods - 1) : '')
			. implode(str_repeat("\n", $this->linesBetweenMethods), $methods),
		]);

		if ($class instanceof ClassType) {
			$line[] = $class->isAbstract() ? 'abstract' : null;
			$line[] = $class->isFinal() ? 'final' : null;
			$line[] = $class->isReadOnly() ? 'readonly' : null;
		}

		$line[] = match (true) {
			$class instanceof ClassType => $class->getName() ? 'class ' . $class->getName() : null,
			$class instanceof InterfaceType => 'interface ' . $class->getName(),
			$class instanceof TraitType => 'trait ' . $class->getName(),
			$class instanceof EnumType => 'enum ' . $class->getName() . ($enumType ? $this->returnTypeColon . $enumType : ''),
		};
		$line[] = ($class instanceof ClassType || $class instanceof InterfaceType) && $class->getExtends()
			? 'extends ' . implode(', ', array_map($resolver, (array) $class->getExtends()))
			: null;
		$line[] = ($class instanceof ClassType || $class instanceof EnumType) && $class->getImplements()
			? 'implements ' . implode(', ', array_map($resolver, $class->getImplements()))
			: null;
		$line[] = $class->getName() ? null : '{';

		return $this->printDocComment($class)
			. $this->printAttributes($class->getAttributes())
			. implode(' ', array_filter($line))
			. ($class->getName() ? "\n{\n" : "\n")
			. ($members ? $this->indent(implode("\n", $members)) : '')
			. '}'
			. ($class->getName() ? "\n" : '');
	}


	public function printNamespace(PhpNamespace $namespace): string
	{
		$this->namespace = $this->resolveTypes ? $namespace : null;
		$name = $namespace->getName();
		$uses = [
			$this->printUses($namespace),
			$this->printUses($namespace, PhpNamespace::NameFunction),
			$this->printUses($namespace, PhpNamespace::NameConstant),
		];
		$uses = implode(str_repeat("\n", $this->linesBetweenUseTypes), array_filter($uses));

		$items = [];
		foreach ($namespace->getClasses() as $class) {
			$items[] = $this->printClass($class, $namespace);
		}

		foreach ($namespace->getFunctions() as $function) {
			$items[] = $this->printFunction($function, $namespace);
		}

		$body = ($uses ? $uses . "\n" : '')
			. implode("\n", $items);

		if ($namespace->hasBracketedSyntax()) {
			return 'namespace' . ($name ? " $name" : '') . "\n{\n"
				. $this->indent($body)
				. "}\n";

		} else {
			return ($name ? "namespace $name;\n\n" : '')
				. $body;
		}
	}


	public function printFile(PhpFile $file): string
	{
		$namespaces = [];
		foreach ($file->getNamespaces() as $namespace) {
			if (!$this->omitEmptyNamespaces || $namespace->getClasses() || $namespace->getFunctions()) {
				$namespaces[] = $this->printNamespace($namespace);
			}
		}

		return "<?php\n"
			. ($file->getComment() ? "\n" . $this->printDocComment($file) : '')
			. "\n"
			. ($file->hasStrictTypes() ? "declare(strict_types=1);\n\n" : '')
			. implode("\n\n", $namespaces);
	}


	protected function printUses(PhpNamespace $namespace, string $of = PhpNamespace::NameNormal): string
	{
		$prefix = [
			PhpNamespace::NameNormal => '',
			PhpNamespace::NameFunction => 'function ',
			PhpNamespace::NameConstant => 'const ',
		][$of];
		$uses = [];
		foreach ($namespace->getUses($of) as $alias => $original) {
			$uses[] = Helpers::extractShortName($original) === $alias
				? "use $prefix$original;\n"
				: "use $prefix$original as $alias;\n";
		}

		return implode('', $uses);
	}


	protected function printParameters(Closure|GlobalFunction|Method|PropertyHook $function, int $column = 0): string
	{
		$special = false;
		foreach ($function->getParameters() as $param) {
			$param->validate();
			$special = $special || $param instanceof PromotedParameter || $param->getAttributes() || $param->getComment();
		}

		if (!$special || ($this->singleParameterOnOneLine && count($function->getParameters()) === 1)) {
			$line = $this->formatParameters($function, multiline: false);
			if (!str_contains($line, "\n") && strlen($line) + $column <= $this->wrapLength) {
				return $line;
			}
		}

		return $this->formatParameters($function, multiline: true);
	}


	private function formatParameters(Closure|GlobalFunction|Method|PropertyHook $function, bool $multiline): string
	{
		$params = $function->getParameters();
		$res = '';

		foreach ($params as $param) {
			$variadic = !$function instanceof PropertyHook && $function->isVariadic() && $param === end($params);
			$attrs = $this->printAttributes($param->getAttributes(), inline: true);
			$res .=
				$this->printDocComment($param)
				. ($attrs ? ($multiline ? substr($attrs, 0, -1) . "\n" : $attrs) : '')
				. ($param instanceof PromotedParameter
					? $this->printPropertyVisibility($param) . ($param->isReadOnly() && $param->getType() ? ' readonly' : '') . ' '
					: '')
				. ltrim($this->printType($param->getType(), $param->isNullable()) . ' ')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . $this->dump($param->getDefaultValue()) : '')
				. ($param instanceof PromotedParameter ? $this->printHooks($param) : '')
				. ($multiline ? ",\n" : ', ');
		}

		return $multiline
			? "(\n" . $this->indent($res) . ')'
			: '(' . substr($res, 0, -2) . ')';
	}


	private function printConstant(Constant $const): string
	{
		$def = ($const->isFinal() ? 'final ' : '')
			. ($const->getVisibility() ? $const->getVisibility() . ' ' : '')
			. 'const '
			. ltrim($this->printType($const->getType(), nullable: false) . ' ')
			. $const->getName() . ' = ';

		return $this->printDocComment($const)
			. $this->printAttributes($const->getAttributes())
			. $def
			. $this->dump($const->getValue(), strlen($def)) . ";\n";
	}


	private function printProperty(Property $property, bool $readOnlyClass = false, bool $isInterface = false): string
	{
		$property->validate();
		$type = $property->getType();
		$def = ($property->isAbstract() && !$isInterface ? 'abstract ' : '')
			. ($property->isFinal() ? 'final ' : '')
			. $this->printPropertyVisibility($property)
			. ($property->isStatic() ? ' static' : '')
			. (!$readOnlyClass && $property->isReadOnly() && $type ? ' readonly' : '')
			. ' '
			. ltrim($this->printType($type, $property->isNullable()) . ' ')
			. '$' . $property->getName();

		$defaultValue = $property->getValue() === null && !$property->isInitialized()
			? ''
			: ' = ' . $this->dump($property->getValue(), strlen($def) + 3); // 3 = ' = '

		return $this->printDocComment($property)
			. $this->printAttributes($property->getAttributes())
			. $def
			. $defaultValue
			. ($this->printHooks($property, $isInterface) ?: ';')
			. "\n";
	}


	private function printPropertyVisibility(Property|PromotedParameter $param): string
	{
		$get = $param->getVisibility(PropertyAccessMode::Get);
		$set = $param->getVisibility(PropertyAccessMode::Set);
		return $set
			? ($get ? "$get $set(set)" : "$set(set)")
			: $get ?? 'public';
	}


	protected function printType(?string $type, bool $nullable): string
	{
		if ($type === null) {
			return '';
		}

		if ($this->namespace) {
			$type = $this->namespace->simplifyType($type);
		}

		return $nullable
			? Type::nullable($type)
			: $type;
	}


	protected function printDocComment(/*Traits\CommentAware*/ $commentable): string
	{
		$multiLine = $commentable instanceof GlobalFunction
			|| $commentable instanceof Method
			|| $commentable instanceof ClassLike
			|| $commentable instanceof PhpFile;
		return Helpers::formatDocComment((string) $commentable->getComment(), $multiLine);
	}


	protected function printReturnType(Closure|GlobalFunction|Method $function): string
	{
		return ($tmp = $this->printType($function->getReturnType(), $function->isReturnNullable()))
			? $this->returnTypeColon . $tmp
			: '';
	}


	/** @param  Attribute[]  $attrs */
	protected function printAttributes(array $attrs, bool $inline = false): string
	{
		if (!$attrs) {
			return '';
		}

		$this->dumper->indentation = $this->indentation;
		$items = [];
		foreach ($attrs as $attr) {
			$args = $this->dumper->format('...?:', $attr->getArguments());
			$args = Helpers::simplifyTaggedNames($args, $this->namespace);
			$items[] = $this->printType($attr->getName(), nullable: false) . ($args === '' ? '' : "($args)");
			$inline = $inline && !str_contains($args, "\n");
		}

		return $inline
			? '#[' . implode(', ', $items) . '] '
			: '#[' . implode("]\n#[", $items) . "]\n";
	}


	private function printHooks(Property|PromotedParameter $property, bool $isInterface = false): string
	{
		$hooks = $property->getHooks();
		if (!$hooks) {
			return '';
		}

		$simple = true;
		foreach ($property->getHooks() as $type => $hook) {
			$simple = $simple && ($hook->isAbstract() || $isInterface);
			$hooks[$type] = $this->printDocComment($hook)
				. $this->printAttributes($hook->getAttributes())
				. ($hook->isAbstract() || $isInterface
					? ($hook->getReturnReference() ? '&' : '')
						. $type . ';'
					: ($hook->isFinal() ? 'final ' : '')
						. ($hook->getReturnReference() ? '&' : '')
						. $type
						. ($hook->getParameters() ? $this->printParameters($hook) : '')
						. ' '
						. ($hook->isShort()
							? '=> ' . $hook->getBody() . ';'
							: "{\n" . $this->indent($this->printFunctionBody($hook)) . '}'));
		}

		return $simple
			? ' { ' . implode(' ', $hooks) . ' }'
			: " {\n" . $this->indent(implode("\n", $hooks)) . "\n}";
	}


	public function setTypeResolving(bool $state = true): static
	{
		$this->resolveTypes = $state;
		return $this;
	}


	protected function indent(string $s): string
	{
		$s = str_replace("\t", $this->indentation, $s);
		return Strings::indent($s, 1, $this->indentation);
	}


	protected function dump(mixed $var, int $column = 0): string
	{
		$this->dumper->indentation = $this->indentation;
		$this->dumper->wrapLength = $this->wrapLength;
		$s = $this->dumper->dump($var, $column);
		$s = Helpers::simplifyTaggedNames($s, $this->namespace);
		return $s;
	}


	/** @param  string[]  $props */
	private function joinProperties(array $props): string
	{
		return $this->linesBetweenProperties
			? implode(str_repeat("\n", $this->linesBetweenProperties), $props)
			: preg_replace('#^(\w.*\n)\n(?=\w.*;)#m', '$1', implode("\n", $props));
	}


	protected function isBraceOnNextLine(bool $multiLine, bool $hasReturnType): bool
	{
		return $this->bracesOnNextLine && (!$multiLine || $hasReturnType);
	}
}
