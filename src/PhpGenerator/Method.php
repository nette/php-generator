<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Method or function description.
 *
 * @property string $body
 */
class Method
{
	use Nette\SmartObject;

	/** @var string|NULL */
	private $name;

	/** @var array of name => Parameter */
	private $parameters = [];

	/** @var array of name => bool */
	private $uses = [];

	/** @var string|FALSE */
	private $body = '';

	/** @var bool */
	private $static = FALSE;

	/** @var string|NULL  public|protected|private */
	private $visibility;

	/** @var bool */
	private $final = FALSE;

	/** @var bool */
	private $abstract = FALSE;

	/** @var bool */
	private $returnReference = FALSE;

	/** @var bool */
	private $variadic = FALSE;

	/** @var string|NULL */
	private $comment;

	/** @var PhpNamespace|NULL */
	private $namespace;

	/** @var string|NULL */
	private $returnType;


	/**
	 * @return static
	 */
	public static function from($from)
	{
		if (is_string($from) && strpos($from, '::')) {
			$from = new \ReflectionMethod($from);
		} elseif (is_array($from)) {
			$from = new \ReflectionMethod($from[0], $from[1]);
		} elseif (!$from instanceof \ReflectionFunctionAbstract) {
			$from = new \ReflectionFunction($from);
		}

		$method = new static($from->isClosure() ? NULL : $from->getName());
		foreach ($from->getParameters() as $param) {
			$method->parameters[$param->getName()] = Parameter::from($param);
		}
		if ($from instanceof \ReflectionMethod) {
			$method->static = $from->isStatic();
			$method->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : NULL);
			$method->final = $from->isFinal();
			$method->abstract = $from->isAbstract() && !$from->getDeclaringClass()->isInterface();
			$method->body = $from->isAbstract() ? FALSE : '';
		}
		$method->returnReference = $from->returnsReference();
		$method->variadic = $from->isVariadic();
		$method->comment = $from->getDocComment() ? preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n\t")) : NULL;
		if (PHP_VERSION_ID >= 70000 && $from->hasReturnType()) {
			$method->returnType = (string) $from->getReturnType();
		}
		return $method;
	}


	/**
	 * @param  string|NULL
	 */
	public function __construct($name = NULL)
	{
		$this->setName($name);
	}


	/**
	 * @return string  PHP code
	 */
	public function __toString()
	{
		$parameters = [];
		foreach ($this->parameters as $param) {
			$variadic = $this->variadic && $param === end($this->parameters);
			$hint = $param->getTypeHint();
			$parameters[] = ($hint ? ($this->namespace ? $this->namespace->unresolveName($hint) : $hint) . ' ' : '')
				. ($param->isReference() ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->getName()
				. ($param->hasDefaultValue() && !$variadic ? ' = ' . Helpers::dump($param->defaultValue) : '');
		}
		$uses = [];
		foreach ($this->uses as $param) {
			$uses[] = ($param->isReference() ? '&' : '') . '$' . $param->getName();
		}

		return ($this->comment ? str_replace("\n", "\n * ", "/**\n" . $this->comment) . "\n */\n" : '')
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->visibility ? $this->visibility . ' ' : '')
			. ($this->static ? 'static ' : '')
			. 'function'
			. ($this->returnReference ? ' &' : '')
			. ' ' . $this->name
			. '(' . implode(', ', $parameters) . ')'
			. ($this->uses ? ' use (' . implode(', ', $uses) . ')' : '')
			. ($this->returnType ? ': ' . ($this->namespace ? $this->namespace->unresolveName($this->returnType) : $this->returnType) : '')
			. ($this->abstract || $this->body === FALSE ? ';'
				: ($this->name ? "\n" : ' ') . "{\n" . Nette\Utils\Strings::indent(ltrim(rtrim($this->body) . "\n"), 1) . '}');
	}


	/** @deprecated */
	public function setName($name)
	{
		$this->name = $name ? (string) $name : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param  Parameter[]
	 * @return static
	 */
	public function setParameters(array $val)
	{
		$this->parameters = [];
		foreach ($val as $v) {
			if (!$v instanceof Parameter) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Parameter[].');
			}
			$this->parameters[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Parameter[]
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * @param  string  without $
	 * @return Parameter
	 */
	public function addParameter($name, $defaultValue = NULL)
	{
		$param = new Parameter($name);
		if (func_num_args() > 1) {
			$param->setOptional(TRUE)->setDefaultValue($defaultValue);
		}
		return $this->parameters[$name] = $param;
	}


	/**
	 * @return static
	 */
	public function setUses(array $val)
	{
		$this->uses = $val;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getUses()
	{
		return $this->uses;
	}


	/**
	 * @return Parameter
	 */
	public function addUse($name)
	{
		return $this->uses[] = new Parameter($name);
	}


	/**
	 * @return static
	 */
	public function setBody($statement, array $args = NULL)
	{
		$this->body = func_num_args() > 1 ? Helpers::formatArgs($statement, $args) : $statement;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}


	/**
	 * @return static
	 */
	public function addBody($statement, array $args = NULL)
	{
		$this->body .= (func_num_args() > 1 ? Helpers::formatArgs($statement, $args) : $statement) . "\n";
		return $this;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setStatic($val)
	{
		$this->static = (bool) $val;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->static;
	}


	/**
	 * @param  string|NULL  public|protected|private
	 * @return static
	 */
	public function setVisibility($val)
	{
		if (!in_array($val, ['public', 'protected', 'private', NULL], TRUE)) {
			throw new Nette\InvalidArgumentException('Argument must be public|protected|private|NULL.');
		}
		$this->visibility = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setFinal($val)
	{
		$this->final = (bool) $val;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return $this->final;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setAbstract($val)
	{
		$this->abstract = (bool) $val;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return $this->abstract;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setReturnReference($val)
	{
		$this->returnReference = (bool) $val;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getReturnReference()
	{
		return $this->returnReference;
	}


	/**
	 * @param  bool
	 * @return static
	 */
	public function setVariadic($val)
	{
		$this->variadic = (bool) $val;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isVariadic()
	{
		return $this->variadic;
	}


	/**
	 * @param  string|NULL
	 * @return static
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
	 * @return static
	 */
	public function addComment($val)
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}


	/** @deprecated */
	public function setDocuments(array $s)
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar setComment()', E_USER_DEPRECATED);
		return $this->setComment(implode("\n", $s));
	}


	/** @deprecated */
	public function getDocuments()
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar getComment()', E_USER_DEPRECATED);
		return $this->comment ? [$this->comment] : [];
	}


	/** @deprecated */
	public function addDocument($s)
	{
		trigger_error(__METHOD__ . '() is deprecated, use addComment()', E_USER_DEPRECATED);
		return $this->addComment($s);
	}


	/**
	 * @return static
	 */
	public function setNamespace(PhpNamespace $val = NULL)
	{
		$this->namespace = $val;
		return $this;
	}


	/**
	 * @param  string|NULL
	 * @return static
	 */
	public function setReturnType($val)
	{
		$this->returnType = $val ? (string) $val : NULL;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getReturnType()
	{
		return $this->returnType;
	}

}
