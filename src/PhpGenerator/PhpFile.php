<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * Instance of PHP file
 *
 * Generates:
 *
 * - opening tag (<?php)
 * - doc comments (if present)
 * - one or more fragments {@link PhpNamespace}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class PhpFile extends Object
{

	/** @var string[] */
	private $documents;

	/** @var boolean */
	private $bracketedNamespaceSyntax = FALSE;

	/** @var PhpNamespace[] */
	private $namespaces = array();

	/**
	 * @return string[]
	 */
	public function getDocuments()
	{
		return $this->documents;
	}


	/**
	 * @param string[] $documents
	 * @return $this
	 */
	public function setDocuments(array $documents)
	{
		$this->documents = $documents;
		return $this;
	}


	/**
	 * @param string $document
	 * @return $this
	 */
	public function addDocument($document)
	{
		$this->documents = (array)$this->documents;
		$this->documents[] = $document;
		return $this;
	}


	/**
	 * @param string $fqn
	 * @return ClassType
	 */
	public function addClass($fqn)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($fqn))
			->addClass(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $fqn
	 * @return ClassType
	 */
	public function addInterface($fqn)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($fqn))
			->addInterface(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $fqn
	 * @return ClassType
	 */
	public function addTrait($fqn)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($fqn))
			->addTrait(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $name NULL means global namespace
	 * @return PhpNamespace
	 */
	public function addNamespace($name)
	{
		if (!isset($this->namespaces[$name])) {
			if (empty($name) && !$this->bracketedNamespaceSyntax) {
				$this->bracketedNamespaceSyntax = TRUE;
				foreach ($this->namespaces as $namespace) {
					$namespace->setBracketedNamespaceSyntax(true);
				}
			}

			$this->namespaces[$name] = new PhpNamespace($name);

			if ($this->bracketedNamespaceSyntax) {
				$this->namespaces[$name]->setBracketedNamespaceSyntax(TRUE);
			}
		}

		return $this->namespaces[$name];
	}


	/**
	 * @return string PHP code
	 */
	public function __toString()
	{
		return Strings::normalize(
			"<?php\n" .
			($this->documents ? "\n" . str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$this->documents)) . "\n */\n\n" : '') .
			implode("\n\n", array_map(function (PhpNamespace $fragment) {
				return (string)$fragment;
			}, $this->namespaces))
		) . "\n";
	}

}
