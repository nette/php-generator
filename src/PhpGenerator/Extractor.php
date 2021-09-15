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

	private $code;
	private $statements;


	public function __construct(string $code)
	{
		if (!class_exists(ParserFactory::class)) {
			throw new Nette\NotSupportedException("PHP-Parser is required to load method bodies, install package 'nikic/php-parser'.");
		}
		$this->parseCode($code);
	}


	private function parseCode(string $code): void
	{
		$this->code = str_replace("\r\n", "\n", $code);
		$lexer = new PhpParser\Lexer(['usedAttributes' => ['startFilePos', 'endFilePos']]);
		$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, $lexer);
		$stmts = $parser->parse($this->code);

		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['replaceNodes' => false]));
		$this->statements = $traverser->traverse($stmts);
	}


	public function extractMethodBodies(string $className): array
	{
		$nodeFinder = new NodeFinder;
		$classNode = $nodeFinder->findFirst($this->statements, function (Node $node) use ($className) {
			return ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Trait_)
				&& $node->namespacedName->toString() === $className;
		});

		$res = [];
		foreach ($nodeFinder->findInstanceOf($classNode, Node\Stmt\ClassMethod::class) as $methodNode) {
			/** @var Node\Stmt\ClassMethod $methodNode */
			if ($methodNode->stmts) {
				$body = $this->extractBody($nodeFinder, $methodNode->stmts);
				$res[$methodNode->name->toString()] = Helpers::unindent($body, 2);
			}
		}
		return $res;
	}


	public function extractFunctionBody(string $name): ?string
	{
		$nodeFinder = new NodeFinder;
		/** @var Node\Stmt\Function_ $functionNode */
		$functionNode = $nodeFinder->findFirst($this->statements, function (Node $node) use ($name) {
			return $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $name;
		});

		$body = $this->extractBody($nodeFinder, $functionNode->stmts);
		return Helpers::unindent($body, 1);
	}


	/**
	 * @param  Node[]  $statements
	 */
	private function extractBody(NodeFinder $nodeFinder, array $statements): string
	{
		$start = $statements[0]->getAttribute('startFilePos');
		$body = substr($this->code, $start, end($statements)->getAttribute('endFilePos') - $start + 1);

		$replacements = [];
		// name-nodes => resolved fully-qualified name
		foreach ($nodeFinder->findInstanceOf($statements, Node\Name::class) as $node) {
			if ($node->hasAttribute('resolvedName')
				&& $node->getAttribute('resolvedName') instanceof Node\Name\FullyQualified
			) {
				$replacements[] = [
					$node->getStartFilePos(),
					$node->getEndFilePos(),
					$node->getAttribute('resolvedName')->toCodeString(),
				];
			}
		}

		// multi-line strings => singleline
		foreach (array_merge(
			$nodeFinder->findInstanceOf($statements, Node\Scalar\String_::class),
			$nodeFinder->findInstanceOf($statements, Node\Scalar\EncapsedStringPart::class)
		) as $node) {
			/** @var Node\Scalar\String_|Node\Scalar\EncapsedStringPart $node */
			$token = substr($body, $node->getStartFilePos() - $start, $node->getEndFilePos() - $node->getStartFilePos() + 1);
			if (strpos($token, "\n") !== false) {
				$quote = $node instanceof Node\Scalar\String_ ? '"' : '';
				$replacements[] = [
					$node->getStartFilePos(),
					$node->getEndFilePos(),
					$quote . addcslashes($node->value, "\x00..\x1F") . $quote,
				];
			}
		}

		// HEREDOC => "string"
		foreach ($nodeFinder->findInstanceOf($statements, Node\Scalar\Encapsed::class) as $node) {
			/** @var Node\Scalar\Encapsed $node */
			if ($node->getAttribute('kind') === Node\Scalar\String_::KIND_HEREDOC) {
				$replacements[] = [
					$node->getStartFilePos(),
					$node->parts[0]->getStartFilePos() - 1,
					'"',
				];
				$replacements[] = [
					end($node->parts)->getEndFilePos() + 1,
					$node->getEndFilePos(),
					'"',
				];
			}
		}

		//sort collected resolved names by position in file
		usort($replacements, function ($a, $b) {
			return $a[0] <=> $b[0];
		});
		$correctiveOffset = -$start;
		//replace changes body length so we need correct offset
		foreach ($replacements as [$startPos, $endPos, $replacement]) {
			$replacingStringLength = $endPos - $startPos + 1;
			$body = substr_replace(
				$body,
				$replacement,
				$correctiveOffset + $startPos,
				$replacingStringLength
			);
			$correctiveOffset += strlen($replacement) - $replacingStringLength;
		}
		return $body;
	}
}
