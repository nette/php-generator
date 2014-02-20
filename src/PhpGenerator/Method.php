<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class method description.
 *
 * @author     David Grudl
 *
 * @method Method setName(string)
 * @method string getName()
 * @method Method setParameters(Parameter[])
 * @method Parameter[] getParameters()
 * @method Method setUses(array)
 * @method array getUses()
 * @method string getBody()
 * @method Method setStatic(bool)
 * @method bool isStatic()
 * @method Method setVisibility(string)
 * @method string getVisibility()
 * @method Method setFinal(bool)
 * @method bool isFinal()
 * @method Method setAbstract(bool)
 * @method bool isAbstract()
 * @method Method setReturnReference(bool)
 * @method bool getReturnReference()
 * @method Method setVariadic(bool)
 * @method bool isVariadic()
 * @method Method setDocuments(string[])
 * @method string[] getDocuments()
 * @method Method addDocument(string)
 */
class Method extends Nette\Object
{
	/** @var string */
	private $name;

	/** @var array of name => Parameter */
	private $parameters = array();

	/** @var array of name => bool */
	private $uses = array();

	/** @var string|FALSE */
	private $body;

	/** @var bool */
	private $static;

	/** @var string  public|protected|private or none */
	private $visibility;

	/** @var bool */
	private $final;

	/** @var bool */
	private $abstract;

	/** @var bool */
	private $returnReference;

	/** @var bool */
	private $variadic;

	/** @var array of string */
	private $documents = array();


	/** @return Method */
	public static function from($from)
	{
		$from = $from instanceof \ReflectionMethod ? $from : new \ReflectionMethod($from);
		$method = new static;
		$method->name = $from->getName();
		foreach ($from->getParameters() as $param) {
			$method->parameters[$param->getName()] = Parameter::from($param);
		}
		$method->static = $from->isStatic();
		$method->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : '');
		$method->final = $from->isFinal();
		$method->abstract = $from->isAbstract() && !$from->getDeclaringClass()->isInterface();
		$method->body = $from->isAbstract() ? FALSE : '';
		$method->returnReference = $from->returnsReference();
		$method->variadic = PHP_VERSION_ID >= 50600 && $from->isVariadic();
		$method->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n"));
		return $method;
	}


	/** @return Parameter */
	public function addParameter($name, $defaultValue = NULL)
	{
		$param = new Parameter;
		if (func_num_args() > 1) {
			$param->setOptional(TRUE)->setDefaultValue($defaultValue);
		}
		return $this->parameters[$name] = $param->setName($name);
	}


	/** @return Parameter */
	public function addUse($name)
	{
		$param = new Parameter;
		return $this->uses[] = $param->setName($name);
	}


	/** @return Method */
	public function setBody($statement, array $args = NULL)
	{
		$this->body = func_num_args() > 1 ? Helpers::formatArgs($statement, $args) : $statement;
		return $this;
	}


	/** @return Method */
	public function addBody($statement, array $args = NULL)
	{
		$this->body .= (func_num_args() > 1 ? Helpers::formatArgs($statement, $args) : $statement) . "\n";
		return $this;
	}


	/** @return string  PHP code */
	public function __toString()
	{
		$parameters = array();
		foreach ($this->parameters as $param) {
			$variadic = $this->variadic && $param === end($this->parameters);
			$parameters[] = ($param->typeHint ? $param->typeHint . ' ' : '')
				. ($param->reference ? '&' : '')
				. ($variadic ? '...' : '')
				. '$' . $param->name
				. ($param->optional && !$variadic ? ' = ' . Helpers::dump($param->defaultValue) : '');
		}
		$uses = array();
		foreach ($this->uses as $param) {
			$uses[] = ($param->reference ? '&' : '') . '$' . $param->name;
		}
		return ($this->documents ? str_replace("\n", "\n * ", "/**\n" . implode("\n", (array) $this->documents)) . "\n */\n" : '')
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->visibility ? $this->visibility . ' ' : '')
			. ($this->static ? 'static ' : '')
			. 'function'
			. ($this->returnReference ? ' &' : '')
			. ($this->name ? ' ' . $this->name : '')
			. '(' . implode(', ', $parameters) . ')'
			. ($this->uses ? ' use (' . implode(', ', $uses) . ')' : '')
			. ($this->abstract || $this->body === FALSE ? ';'
				: ($this->name ? "\n" : ' ') . "{\n" . Nette\Utils\Strings::indent(trim($this->body), 1) . "\n}");
	}

}
