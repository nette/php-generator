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
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;


/**
 * Extracts information from PHP code.
 * @internal
 */
final class Extractor
{
	private string $code;

	/** @var Node[] */
	private array $statements;
	private PhpParser\PrettyPrinterAbstract $printer;


	public function __construct(string $code)
	{
		if (!class_exists(ParserFactory::class)) {
			throw new Nette\NotSupportedException("PHP-Parser is required to load method bodies, install package 'nikic/php-parser' 4.7 or newer.");
		}

		$this->printer = new PhpParser\PrettyPrinter\Standard;
		$this->parseCode($code);
	}


	private function parseCode(string $code): void
	{
		if (!str_starts_with($code, '<?php')) {
			throw new Nette\InvalidStateException('The input string is not a PHP code.');
		}

        $this->code = Nette\Utils\Strings::unixNewLines($code);
        $parser = (new ParserFactory)->createForNewestSupportedVersion();
		$stmts = $parser->parse($this->code);

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\ParentConnectingVisitor);
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['preserveOriginalNames' => true]));
		$this->statements = $traverser->traverse($stmts);
	}


	/** @return array<string, string> */
	public function extractMethodBodies(string $className): array
	{
		$nodeFinder = new NodeFinder;
		$classNode = $nodeFinder->findFirst(
			$this->statements,
			fn(Node $node) => $node instanceof Node\Stmt\ClassLike && $node->namespacedName->toString() === $className,
		);

		$res = [];
		foreach ($nodeFinder->findInstanceOf($classNode, Node\Stmt\ClassMethod::class) as $methodNode) {
			assert($methodNode instanceof Node\Stmt\ClassMethod);
			if ($methodNode->stmts) {
				$res[$methodNode->name->toString()] = $this->getReformattedContents($methodNode->stmts, 2);
			}
		}

		return $res;
	}


	public function extractFunctionBody(string $name): ?string
	{
		$functionNode = (new NodeFinder)->findFirst(
			$this->statements,
			fn(Node $node) => $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $name,
		);
		assert($functionNode instanceof Node\Stmt\Function_);

		return $this->getReformattedContents($functionNode->stmts, 1);
	}


	/** @param  Node[]  $nodes */
	private function getReformattedContents(array $nodes, int $level): string
	{
		$body = $this->getNodeContents(...$nodes);
		$body = $this->performReplacements($body, $this->prepareReplacements($nodes));
		return Helpers::unindent($body, $level);
	}


	/**
	 * @param  Node[]  $nodes
	 * @return array<array{int, int, string}>
	 */
	private function prepareReplacements(array $nodes): array
	{
		$start = $this->getNodeStartPos($nodes[0]);
		$replacements = [];
		(new NodeFinder)->find($nodes, function (Node $node) use (&$replacements, $start) {
			if ($node instanceof Node\Name\FullyQualified) {
				if ($node->getAttribute('originalName') instanceof Node\Name) {
					$of = match (true) {
						$node->getAttribute('parent') instanceof Node\Expr\ConstFetch => PhpNamespace::NameConstant,
						$node->getAttribute('parent') instanceof Node\Expr\FuncCall => PhpNamespace::NameFunction,
						default => PhpNamespace::NameNormal,
					};
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->getEndFilePos() - $start,
						Helpers::tagName($node->toCodeString(), $of),
					];
				}
			} elseif ($node instanceof Node\Scalar\String_ || $node instanceof Node\Scalar\EncapsedStringPart) {
				// multi-line strings => singleline
				$token = $this->getNodeContents($node);
				if (str_contains($token, "\n")) {
					$quote = $node instanceof Node\Scalar\String_ ? '"' : '';
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->getEndFilePos() - $start,
						$quote . addcslashes($node->value, "\x00..\x1F") . $quote,
					];
				}
			} elseif ($node instanceof Node\Scalar\Encapsed) {
				// HEREDOC => "string"
				if ($node->getAttribute('kind') === Node\Scalar\String_::KIND_HEREDOC) {
					$replacements[] = [
						$node->getStartFilePos() - $start,
						$node->parts[0]->getStartFilePos() - $start - 1,
						'"',
					];
					$replacements[] = [
						end($node->parts)->getEndFilePos() - $start + 1,
						$node->getEndFilePos() - $start,
						'"',
					];
				}
			}
		});
		return $replacements;
	}


	/** @param  array<array{int, int, string}>  $replacements */
	private function performReplacements(string $s, array $replacements): string
	{
		usort($replacements, fn($a, $b) => $b[0] <=> $a[0]);

		foreach ($replacements as [$start, $end, $replacement]) {
			$s = substr_replace($s, $replacement, $start, $end - $start + 1);
		}

		return $s;
	}


	public function extractAll(): PhpFile
	{
		$phpFile = new PhpFile;

		if (
			$this->statements
			&& !$this->statements[0] instanceof Node\Stmt\ClassLike
			&& !$this->statements[0] instanceof Node\Stmt\Function_
		) {
			$this->addCommentAndAttributes($phpFile, $this->statements[0]);
		}

		$namespaces = ['' => $this->statements];
		foreach ($this->statements as $node) {
			if ($node instanceof Node\Stmt\Declare_
				&& $node->declares[0] instanceof Node\Stmt\DeclareDeclare
				&& $node->declares[0]->key->name === 'strict_types'
				&& $node->declares[0]->value instanceof Node\Scalar\LNumber
			) {
				$phpFile->setStrictTypes((bool) $node->declares[0]->value->value);

			} elseif ($node instanceof Node\Stmt\Namespace_) {
				$namespaces[$node->name->toString()] = $node->stmts;
			}
		}

		foreach ($namespaces as $name => $nodes) {
			foreach ($nodes as $node) {
				match (true) {
					$node instanceof Node\Stmt\Use_ => $this->addUseToNamespace($phpFile->addNamespace($name), $node),
					$node instanceof Node\Stmt\ClassLike => $this->addClassLikeToFile($phpFile, $node),
					$node instanceof Node\Stmt\Function_ => $this->addFunctionToFile($phpFile, $node),
					default => null,
				};
			}
		}

		return $phpFile;
	}


	private function addUseToNamespace(PhpNamespace $namespace, Node\Stmt\Use_ $node): void
	{
		$of = [
			$node::TYPE_NORMAL => PhpNamespace::NameNormal,
			$node::TYPE_FUNCTION => PhpNamespace::NameFunction,
			$node::TYPE_CONSTANT => PhpNamespace::NameConstant,
		][$node->type];
		foreach ($node->uses as $use) {
			$namespace->addUse($use->name->toString(), $use->alias?->toString(), $of);
		}
	}


	private function addClassLikeToFile(PhpFile $phpFile, Node\Stmt\ClassLike $node): ClassLike
	{
		if ($node instanceof Node\Stmt\Class_) {
			$class = $phpFile->addClass($node->namespacedName->toString());
			$class->setFinal($node->isFinal());
			$class->setAbstract($node->isAbstract());
			$class->setReadOnly(method_exists($node, 'isReadonly') && $node->isReadonly());
			if ($node->extends) {
				$class->setExtends($node->extends->toString());
			}
			foreach ($node->implements as $item) {
				$class->addImplement($item->toString());
			}
		} elseif ($node instanceof Node\Stmt\Interface_) {
			$class = $phpFile->addInterface($node->namespacedName->toString());
			foreach ($node->extends as $item) {
				$class->addExtend($item->toString());
			}
		} elseif ($node instanceof Node\Stmt\Trait_) {
			$class = $phpFile->addTrait($node->namespacedName->toString());

		} elseif ($node instanceof Node\Stmt\Enum_) {
			$class = $phpFile->addEnum($node->namespacedName->toString());
			$class->setType($node->scalarType?->toString());
			foreach ($node->implements as $item) {
				$class->addImplement($item->toString());
			}
		}

		$this->addCommentAndAttributes($class, $node);
		$this->addClassMembers($class, $node);
		return $class;
	}


	private function addClassMembers(ClassLike $class, Node\Stmt\ClassLike $node): void
	{
		foreach ($node->stmts as $stmt) {
			match (true) {
				$stmt instanceof Node\Stmt\TraitUse => $this->addTraitToClass($class, $stmt),
				$stmt instanceof Node\Stmt\Property => $this->addPropertyToClass($class, $stmt),
				$stmt instanceof Node\Stmt\ClassMethod => $this->addMethodToClass($class, $stmt),
				$stmt instanceof Node\Stmt\ClassConst => $this->addConstantToClass($class, $stmt),
				$stmt instanceof Node\Stmt\EnumCase => $this->addEnumCaseToClass($class, $stmt),
				default => null,
			};
		}
	}


	private function addTraitToClass(ClassLike $class, Node\Stmt\TraitUse $node): void
	{
		foreach ($node->traits as $item) {
			$trait = $class->addTrait($item->toString());
		}

		foreach ($node->adaptations as $item) {
			$trait->addResolution(rtrim($this->getReformattedContents([$item], 0), ';'));
		}

		$this->addCommentAndAttributes($trait, $node);
	}


	private function addPropertyToClass(ClassLike $class, Node\Stmt\Property $node): void
	{
		foreach ($node->props as $item) {
			$prop = $class->addProperty($item->name->toString());
			$prop->setStatic($node->isStatic());
			$prop->setVisibility($this->toVisibility($node->flags));
			$prop->setType($node->type ? $this->toPhp($node->type) : null);
			if ($item->default) {
				$prop->setValue($this->toValue($item->default));
			}

			$prop->setReadOnly(method_exists($node, 'isReadonly') && $node->isReadonly());
			$this->addCommentAndAttributes($prop, $node);
		}
	}


	private function addMethodToClass(ClassLike $class, Node\Stmt\ClassMethod $node): void
	{
		$method = $class->addMethod($node->name->toString());
		$method->setAbstract($node->isAbstract());
		$method->setFinal($node->isFinal());
		$method->setStatic($node->isStatic());
		$method->setVisibility($this->toVisibility($node->flags));
		$this->setupFunction($method, $node);
	}


	private function addConstantToClass(ClassLike $class, Node\Stmt\ClassConst $node): void
	{
		foreach ($node->consts as $item) {
			$const = $class->addConstant($item->name->toString(), $this->toValue($item->value));
			$const->setVisibility($this->toVisibility($node->flags));
			$const->setFinal(method_exists($node, 'isFinal') && $node->isFinal());
			$this->addCommentAndAttributes($const, $node);
		}
	}


	private function addEnumCaseToClass(EnumType $class, Node\Stmt\EnumCase $node): void
	{
		$value = match (true) {
			$node->expr === null => null,
			$node->expr instanceof Node\Scalar\LNumber, $node->expr instanceof Node\Scalar\String_ => $node->expr->value,
			default => $this->toValue($node->expr),
		};
		$case = $class->addCase($node->name->toString(), $value);
		$this->addCommentAndAttributes($case, $node);
	}


	private function addFunctionToFile(PhpFile $phpFile, Node\Stmt\Function_ $node): void
	{
		$function = $phpFile->addFunction($node->namespacedName->toString());
		$this->setupFunction($function, $node);
	}


	private function addCommentAndAttributes(
		PhpFile|ClassLike|Constant|Property|GlobalFunction|Method|Parameter|EnumCase|TraitUse $element,
		Node $node,
	): void
	{
		if ($node->getDocComment()) {
			$comment = $node->getDocComment()->getReformattedText();
			$comment = Helpers::unformatDocComment($comment);
			$element->setComment($comment);
			$node->setDocComment(new PhpParser\Comment\Doc(''));
		}

		foreach ($node->attrGroups ?? [] as $group) {
			foreach ($group->attrs as $attribute) {
				$args = [];
				foreach ($attribute->args as $arg) {
					if ($arg->name) {
						$args[$arg->name->toString()] = $this->toValue($arg->value);
					} else {
						$args[] = $this->toValue($arg->value);
					}
				}

				$element->addAttribute($attribute->name->toString(), $args);
			}
		}
	}


	private function setupFunction(GlobalFunction|Method $function, Node\FunctionLike $node): void
	{
		$function->setReturnReference($node->returnsByRef());
		$function->setReturnType($node->getReturnType() ? $this->toPhp($node->getReturnType()) : null);
		foreach ($node->getParams() as $item) {
			$visibility = $this->toVisibility($item->flags);
			$isReadonly = (bool) ($item->flags & Node\Stmt\Class_::MODIFIER_READONLY);
			$param = $visibility
				? ($function->addPromotedParameter($item->var->name))->setVisibility($visibility)->setReadonly($isReadonly)
				: $function->addParameter($item->var->name);
			$param->setType($item->type ? $this->toPhp($item->type) : null);
			$param->setReference($item->byRef);
			$function->setVariadic($item->variadic);
			if ($item->default) {
				$param->setDefaultValue($this->toValue($item->default));
			}

			$this->addCommentAndAttributes($param, $item);
		}

		$this->addCommentAndAttributes($function, $node);
		if ($node->getStmts()) {
			$indent = $function instanceof GlobalFunction ? 1 : 2;
			$function->setBody($this->getReformattedContents($node->getStmts(), $indent));
		}
	}


	private function toValue(Node\Expr $node): mixed
	{
		if ($node instanceof Node\Expr\ConstFetch) {
			return match ($node->name->toLowerString()) {
				'null' => null,
				'true' => true,
				'false' => false,
				default => new Literal($this->getReformattedContents([$node], 0)),
			};
		} elseif ($node instanceof Node\Scalar\LNumber
			|| $node instanceof Node\Scalar\DNumber
			|| $node instanceof Node\Scalar\String_
		) {
			return $node->value;

		} elseif ($node instanceof Node\Expr\Array_) {
			$res = [];
			foreach ($node->items as $item) {
				if ($item->unpack) {
					return new Literal($this->getReformattedContents([$node], 0));

				} elseif ($item->key) {
					$key = $item->key instanceof Node\Identifier
						? $item->key->name
						: $this->toValue($item->key);

					if ($key instanceof Literal) {
						return new Literal($this->getReformattedContents([$node], 0));
					}

					$res[$key] = $this->toValue($item->value);

				} else {
					$res[] = $this->toValue($item->value);
				}
			}
			return $res;

		} else {
			return new Literal($this->getReformattedContents([$node], 0));
		}
	}


	private function toVisibility(int $flags): ?string
	{
		return match (true) {
			(bool) ($flags & Node\Stmt\Class_::MODIFIER_PUBLIC) => ClassType::VisibilityPublic,
			(bool) ($flags & Node\Stmt\Class_::MODIFIER_PROTECTED) => ClassType::VisibilityProtected,
			(bool) ($flags & Node\Stmt\Class_::MODIFIER_PRIVATE) => ClassType::VisibilityPrivate,
			default => null,
		};
	}


	private function toPhp(Node $value): string
	{
		$dolly = clone $value;
		$dolly->setAttribute('comments', []);
		return $this->printer->prettyPrint([$dolly]);
	}


	private function getNodeContents(Node ...$nodes): string
	{
		$start = $this->getNodeStartPos($nodes[0]);
		return substr($this->code, $start, end($nodes)->getEndFilePos() - $start + 1);
	}


	private function getNodeStartPos(Node $node): int
	{
		return ($comments = $node->getComments())
			? $comments[0]->getStartFilePos()
			: $node->getStartFilePos();
	}
}
