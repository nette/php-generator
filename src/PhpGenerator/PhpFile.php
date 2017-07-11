<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;


/**
 * Instance of PHP file.
 *
 * Generates:
 * - opening tag (<?php)
 * - doc comments
 * - one or more namespaces
 */
class PhpFile
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	/** @var PhpNamespace[] */
	private $namespaces = [];


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
	 * @param  string null means global namespace
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
			$namespace->setBracketedSyntax(count($this->namespaces) > 1 && isset($this->namespaces[null]));
		}

		return Strings::normalize(
			"<?php\n"
			. ($this->comment ? "\n" . Helpers::formatDocComment($this->comment . "\n") . "\n" : '')
			. implode("\n\n", $this->namespaces)
		) . "\n";
	}
}
