<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\PhpGenerator;

use Nette;


/**
 * Class property description.
 *
 * @author     David Grudl
 *
 * @method Property setName(string)
 * @method string getName()
 * @method Property setValue(mixed)
 * @method mixed getValue()
 * @method Property setStatic(bool)
 * @method bool isStatic()
 * @method Property setVisibility(string)
 * @method string getVisibility()
 * @method Property setDocuments(string[])
 * @method string[] getDocuments()
 * @method Property addDocument(string)
 */
class Property extends Nette\Object
{
	/** @var string */
	private $name;

	/** @var mixed */
	public $value;

	/** @var bool */
	private $static;

	/** @var string  public|protected|private */
	private $visibility = 'public';

	/** @var array of string */
	private $documents = array();


	/** @return Property */
	public static function from(\ReflectionProperty $from)
	{
		$prop = new static;
		$prop->name = $from->getName();
		$defaults = $from->getDeclaringClass()->getDefaultProperties();
		$prop->value = isset($defaults[$from->name]) ? $defaults[$from->name] : NULL;
		$prop->static = $from->isStatic();
		$prop->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public');
		$prop->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n"));
		return $prop;
	}

}
