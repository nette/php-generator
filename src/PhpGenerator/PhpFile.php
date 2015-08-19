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
 */
class PhpFile extends Object
{
	/** @var string|NULL */
	private $comment;

	/** @var PhpNamespace[] */
	private $namespaces = [];




	/**
	 * @param  string|NULL
	 * @return self
	 */
	public function setComment($val)
	{
		$this->comment = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getComment()
	{
		return $this->comment;
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function addComment($val)
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}


	/** @deprecated */
	public function setDocuments(array $s)
	{
		return $this->setComment(implode("\n", $s));
	}


	/** @deprecated */
	public function getDocuments()
	{
		return $this->comment ? [$this->comment] : [];
	}


	/** @deprecated */
	public function addDocument($s)
	{
		return $this->addComment($s);
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
			. ($this->comment ? "\n" . str_replace("\n", "\n * ", "/**\n" . $this->comment) . "\n */\n\n" : '')
			. implode("\n\n", $this->namespaces)
		) . "\n";
	}

}
