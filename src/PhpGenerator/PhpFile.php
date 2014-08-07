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
 * - one or more fragments {@link PhpFileFragment}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class PhpFile extends Object
{

	/** @var string[] */
	private $documents;

	/** @var boolean */
	private $bracketedNamespaceSyntax = FALSE;

	/** @var PhpFileFragment[] */
	private $fragments = array();

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
			->addFragment(Helpers::extractNamespace($fqn))
			->addClass(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $fqn
	 * @return ClassType
	 */
	public function addInterface($fqn)
	{
		return $this
			->addFragment(Helpers::extractNamespace($fqn))
			->addInterface(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $fqn
	 * @return ClassType
	 */
	public function addTrait($fqn)
	{
		return $this
			->addFragment(Helpers::extractNamespace($fqn))
			->addTrait(Helpers::extractShortName($fqn));
	}


	/**
	 * @param string $namespace NULL means global namespace
	 * @return PhpFileFragment
	 */
	public function addFragment($namespace)
	{
		if (!isset($this->fragments[$namespace])) {
			if (empty($namespace) && !$this->bracketedNamespaceSyntax) {
				$this->bracketedNamespaceSyntax = TRUE;
				foreach ($this->fragments as $fragment) {
					$fragment->setBracketedNamespaceSyntax(true);
				}
			}

			$this->fragments[$namespace] = new PhpFileFragment($namespace);

			if ($this->bracketedNamespaceSyntax) {
				$this->fragments[$namespace]->setBracketedNamespaceSyntax(TRUE);
			}
		}

		return $this->fragments[$namespace];
	}


	/**
	 * @return string PHP code
	 */
	public function __toString()
	{
		return Strings::normalize(
			"<?php\n" .
			($this->documents ? "\n" . str_replace("\n", "\n * ", "/**\n" . implode("\n", (array)$this->documents)) . "\n */\n\n" : '') .
			implode("\n\n", array_map(function (PhpFileFragment $fragment) {
				return (string)$fragment;
			}, $this->fragments))
		) . "\n";
	}

}
