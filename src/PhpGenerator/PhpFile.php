<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette\Object;
use Nette\Utils\Strings;


/**
 * Instance of PHP file.
 *
 * Generates:
 * - opening tag (<?php)
 * - doc comments
 * - one or more namespaces
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class PhpFile extends Object
{
	/** @var string[] */
	private $documents;

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
	 * @param  string[]
	 * @return self
	 */
	public function setDocuments(array $documents)
	{
		$this->documents = $documents;
		return $this;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function addDocument($document)
	{
		$this->documents[] = $document;
		return $this;
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addClass($name)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addClass(Helpers::extractShortName($name));
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addInterface($name)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addInterface(Helpers::extractShortName($name));
	}


	/**
	 * @param  string
	 * @return ClassType
	 */
	public function addTrait($name)
	{
		return $this
			->addNamespace(Helpers::extractNamespace($name))
			->addTrait(Helpers::extractShortName($name));
	}


	/**
	 * @param  string NULL means global namespace
	 * @return PhpNamespace
	 */
	public function addNamespace($name)
	{
		if (!isset($this->namespaces[$name])) {
			$this->namespaces[$name] = new PhpNamespace($name);
		}
		return $this->namespaces[$name];
	}


	/**
	 * @return string PHP code
	 */
	public function __toString()
	{
		foreach ($this->namespaces as $namespace) {
			$namespace->setBracketedSyntax(isset($this->namespaces[NULL]));
		}

		return Strings::normalize(
			"<?php\n"
			. ($this->documents ? "\n" . str_replace("\n", "\n * ", "/**\n" . implode("\n", (array) $this->documents)) . "\n */\n\n" : '')
			. implode("\n\n", $this->namespaces)
		) . "\n";
	}

}
