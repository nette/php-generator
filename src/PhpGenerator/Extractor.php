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
		$traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver(null, ['preserveOriginalNames' => true]));
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
				$res[$methodNode->name->toString()] = $this->getReformattedBody($methodNode->stmts, 2);
			}
		}
		return $res;
	}


	public function extractFunctionBody(string $name): ?string
	{
		$functionNode = (new NodeFinder)->findFirst($this->statements, function (Node $node) use ($name) {
			return $node instanceof Node\Stmt\Function_ && $node->namespacedName->toString() === $name;
		});

		return $this->getReformattedBody($functionNode->stmts, 1);
	}


	/** @param  Node[]  $statements */
	private function getReformattedBody(array $statements, int $level): string
	{
		$replacements = $this->prepareReplacements($statements);
		$body = $this->getNodeContents(...$statements);
		$body = $this->performReplacements($body, $replacements);
		return Helpers::unindent($body, $level);
	}


	private function prepareReplacements(array $statements): array
	{
		$start = $statements[0]->getStartFilePos();
		$replacements = [];
		$nodeFinder = new NodeFinder;

		// name-nodes => resolved fully-qualified name
		foreach ($nodeFinder->findInstanceOf($statements, Node\Name\FullyQualified::class) as $node) {
			if ($node->hasAttribute('originalName')
				&& $node->getAttribute('originalName') instanceof Node\Name
			) {
				$replacements[] = [
					$node->getStartFilePos() - $start,
					$node->getEndFilePos() - $start,
					$node->toCodeString(),
				];
			}
		}

		// multi-line strings => singleline
		foreach (array_merge(
			$nodeFinder->findInstanceOf($statements, Node\Scalar\String_::class),
			$nodeFinder->findInstanceOf($statements, Node\Scalar\EncapsedStringPart::class)
		) as $node) {
			/** @var Node\Scalar\String_|Node\Scalar\EncapsedStringPart $node */
			$token = $this->getNodeContents($node);
			if (strpos($token, "\n") !== false) {
				$quote = $node instanceof Node\Scalar\String_ ? '"' : '';
				$replacements[] = [
					$node->getStartFilePos() - $start,
					$node->getEndFilePos() - $start,
					$quote . addcslashes($node->value, "\x00..\x1F") . $quote,
				];
			}
		}

		// HEREDOC => "string"
		foreach ($nodeFinder->findInstanceOf($statements, Node\Scalar\Encapsed::class) as $node) {
			/** @var Node\Scalar\Encapsed $node */
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

		return $replacements;
	}


	private function performReplacements(string $s, array $replacements): string
	{
		usort($replacements, function ($a, $b) { // sort by position in file
			return $a[0] <=> $b[0];
		});

		$correctiveOffset = 0;
		foreach ($replacements as [$start, $end, $replacement]) {
			$replacingStringLength = $end - $start + 1;
			$s = substr_replace(
				$s,
				$replacement,
				$correctiveOffset + $start,
				$replacingStringLength
			);
			$correctiveOffset += strlen($replacement) - $replacingStringLength;
		}
		return $s;
	}


	private function getNodeContents(Node ...$nodes): string
	{
		$start = $nodes[0]->getStartFilePos();
		return substr($this->code, $start, end($nodes)->getEndFilePos() - $start + 1);
	}
}
