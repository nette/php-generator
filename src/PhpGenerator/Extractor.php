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
	use Nette\SmartObject;

	private string $code;
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

		$this->code = str_replace("\r\n", "\n", $code);
		$lexer = new PhpParser\Lexer\Emulative(['usedAttributes' => ['startFilePos', 'endFilePos', 'comments']]);
		$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, $lexer);
		$stmts = $parser->parse($this->code);

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\ParentConnectingVisitor);
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['preserveOriginalNames' => true]));
		$this->statements = $traverser->traverse($stmts);
	}


	public function extractMethodBodies(string $className): array
	{
		$nodeFinder = new NodeFinder;
		$classNode = $nodeFinder->findFirst(
			$this->statements,
			fn(Node $node) => ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Trait_)
				&& $node->namespacedName->toString() === $className,
		);

		$res = [];
		foreach ($nodeFinder->findInstanceOf($classNode, Node\Stmt\ClassMethod::class) as $methodNode) {
			/** @var Node\Stmt\ClassMethod $methodNode */
			if ($methodNode->stmts) {
				$res[$methodNode->name->toString()] = $this->getReformattedContents($methodNode->stmts, 2);
			}
		}

		return $res;
	}


	public function extractFunctionBody(string $name): ?string
	{
		/** @var Node\Stmt\Function_ $functionNode */
		$functionNode = (new NodeFinder)->findFirst(
			$this->statements,
			fn(Node $node) => $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $name,
		);

		return $this->getReformattedContents($functionNode->stmts, 1);
	}


	/** @param  Node[]  $statements */
	private function getReformattedContents(array $statements, int $level): string
	{
		$body = $this->getNodeContents(...$statements);
		$body = $this->performReplacements($body, $this->prepareReplacements($statements));
		return Helpers::unindent($body, $level);
	}


	private function prepareReplacements(array $statements): array
	{
		$start = $statements[0]->getStartFilePos();
		$replacements = [];
		(new NodeFinder)->find($statements, function (Node $node) use (&$replacements, $start) {
			if ($node instanceof Node\Name\FullyQualified) {
				if ($node->getAttribute('originalName') instanceof Node\Name) {
					$of = match (true) {
						$node->getAttribute('parent') instanceof Node\Expr\ConstFetch => PhpNamespace::NAME_CONSTANT,
						$node->getAttribute('parent') instanceof Node\Expr\FuncCall => PhpNamespace::NAME_FUNCTION,
						default => PhpNamespace::NAME_NORMAL,
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
		$namespace = '';
		$visitor = new class extends PhpParser\NodeVisitorAbstract {
			public function enterNode(Node $node)
			{
				return ($this->callback)($node);
			}
		};

		$visitor->callback = function (Node $node) use (&$class, &$namespace, $phpFile) {
			if ($node instanceof Node\Stmt\Class_ && !$node->name) {
				return PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
			}
			match (true) {
				$node instanceof Node\Stmt\DeclareDeclare && $node->key->name === 'strict_types' => $phpFile->setStrictTypes((bool) $node->value->value),
				$node instanceof Node\Stmt\Namespace_ => $namespace = $node->name?->toString(),
				$node instanceof Node\Stmt\Use_ => $this->addUseToNamespace($node, $phpFile->addNamespace($namespace)),
				$node instanceof Node\Stmt\Class_ => $class = $this->addClassToFile($phpFile, $node),
				$node instanceof Node\Stmt\Interface_ => $class = $this->addInterfaceToFile($phpFile, $node),
				$node instanceof Node\Stmt\Trait_ => $class = $this->addTraitToFile($phpFile, $node),
				$node instanceof Node\Stmt\Enum_ => $class = $this->addEnumToFile($phpFile, $node),
				$node instanceof Node\Stmt\Function_ => $this->addFunctionToFile($phpFile, $node),
				$node instanceof Node\Stmt\TraitUse => $this->addTraitToClass($class, $node),
				$node instanceof Node\Stmt\Property => $this->addPropertyToClass($class, $node),
				$node instanceof Node\Stmt\ClassMethod => $this->addMethodToClass($class, $node),
				$node instanceof Node\Stmt\ClassConst => $this->addConstantToClass($class, $node),
				$node instanceof Node\Stmt\EnumCase => $this->addEnumCaseToClass($class, $node),
				default => null,
			};
			if ($node instanceof Node\FunctionLike) {
				return PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
			}
		};

		if ($this->statements) {
			$this->addCommentAndAttributes($phpFile, $this->statements[0]);
		}

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->statements);
		return $phpFile;
	}


	private function addUseToNamespace(Node\Stmt\Use_ $node, PhpNamespace $namespace): void
	{
		$of = [
			$node::TYPE_NORMAL => PhpNamespace::NAME_NORMAL,
			$node::TYPE_FUNCTION => PhpNamespace::NAME_FUNCTION,
			$node::TYPE_CONSTANT => PhpNamespace::NAME_CONSTANT,
		][$node->type];
		foreach ($node->uses as $use) {
			$namespace->addUse($use->name->toString(), $use->alias?->toString(), $of);
		}
	}


	private function addClassToFile(PhpFile $phpFile, Node\Stmt\Class_ $node): ClassType
	{
		$class = $phpFile->addClass($node->namespacedName->toString());
		if ($node->extends) {
			$class->setExtends($node->extends->toString());
		}

		foreach ($node->implements as $item) {
			$class->addImplement($item->toString());
		}

		$class->setFinal($node->isFinal());
		$class->setAbstract($node->isAbstract());
		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addInterfaceToFile(PhpFile $phpFile, Node\Stmt\Interface_ $node): ClassType
	{
		$class = $phpFile->addInterface($node->namespacedName->toString());
		foreach ($node->extends as $item) {
			$class->addExtend($item->toString());
		}

		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addTraitToFile(PhpFile $phpFile, Node\Stmt\Trait_ $node): ClassType
	{
		$class = $phpFile->addTrait($node->namespacedName->toString());
		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addEnumToFile(PhpFile $phpFile, Node\Stmt\Enum_ $node): ClassType
	{
		$class = $phpFile->addEnum($node->namespacedName->toString());
		foreach ($node->implements as $item) {
			$class->addImplement($item->toString());
		}

		$this->addCommentAndAttributes($class, $node);
		return $class;
	}


	private function addFunctionToFile(PhpFile $phpFile, Node\Stmt\Function_ $node): void
	{
		$function = $phpFile->addFunction($node->namespacedName->toString());
		$this->setupFunction($function, $node);
	}


	private function addTraitToClass(ClassType $class, Node\Stmt\TraitUse $node): void
	{
		foreach ($node->traits as $item) {
			$trait = $class->addTrait($item->toString(), true);
		}

		foreach ($node->adaptations as $item) {
			$trait->addResolution(trim($this->toPhp($item), ';'));
		}

		$this->addCommentAndAttributes($trait, $node);
	}


	private function addPropertyToClass(ClassType $class, Node\Stmt\Property $node): void
	{
		foreach ($node->props as $item) {
			$prop = $class->addProperty($item->name->toString());
			$prop->setStatic($node->isStatic());
			if ($node->isPrivate()) {
				$prop->setPrivate();
			} elseif ($node->isProtected()) {
				$prop->setProtected();
			}

			$prop->setType($node->type ? $this->toPhp($node->type) : null);
			if ($item->default) {
				$prop->setValue(new Literal($this->getReformattedContents([$item->default], 1)));
			}

			$prop->setReadOnly(method_exists($node, 'isReadonly') && $node->isReadonly());
			$this->addCommentAndAttributes($prop, $node);
		}
	}


	private function addMethodToClass(ClassType $class, Node\Stmt\ClassMethod $node): void
	{
		$method = $class->addMethod($node->name->toString());
		$method->setAbstract($node->isAbstract());
		$method->setFinal($node->isFinal());
		$method->setStatic($node->isStatic());
		if ($node->isPrivate()) {
			$method->setPrivate();
		} elseif ($node->isProtected()) {
			$method->setProtected();
		}

		$this->setupFunction($method, $node);
	}


	private function addConstantToClass(ClassType $class, Node\Stmt\ClassConst $node): void
	{
		foreach ($node->consts as $item) {
			$value = $this->getReformattedContents([$item->value], 1);
			$const = $class->addConstant($item->name->toString(), new Literal($value));
			if ($node->isPrivate()) {
				$const->setPrivate();
			} elseif ($node->isProtected()) {
				$const->setProtected();
			}

			$const->setFinal(method_exists($node, 'isFinal') && $node->isFinal());
			$this->addCommentAndAttributes($const, $node);
		}
	}


	private function addEnumCaseToClass(ClassType $class, Node\Stmt\EnumCase $node): void
	{
		$case = $class->addCase($node->name->toString(), $node->expr?->value);
		$this->addCommentAndAttributes($case, $node);
	}


	private function addCommentAndAttributes($element, Node $node): void
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
					$value = new Literal($this->getReformattedContents([$arg->value], 0));
					if ($arg->name) {
						$args[$arg->name->toString()] = $value;
					} else {
						$args[] = $value;
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
		foreach ($node->params as $item) {
			$param = $function->addParameter($item->var->name);
			$param->setType($item->type ? $this->toPhp($item->type) : null);
			$param->setReference($item->byRef);
			$function->setVariadic($item->variadic);
			if ($item->default) {
				$param->setDefaultValue(new Literal($this->getReformattedContents([$item->default], 2)));
			}

			$this->addCommentAndAttributes($param, $item);
		}

		$this->addCommentAndAttributes($function, $node);
		if ($node->stmts) {
			$function->setBody($this->getReformattedContents($node->stmts, 2));
		}
	}


	private function toPhp(mixed $value): string
	{
		return $this->printer->prettyPrint([$value]);
	}


	private function getNodeContents(Node ...$nodes): string
	{
		$start = $nodes[0]->getStartFilePos();
		return substr($this->code, $start, end($nodes)->getEndFilePos() - $start + 1);
	}
}
