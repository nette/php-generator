Nette PHP Generator
===================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/php-generator.svg)](https://packagist.org/packages/nette/php-generator)
[![Build Status](https://travis-ci.org/nette/php-generator.svg?branch=master)](https://travis-ci.org/nette/php-generator)
[![Coverage Status](https://coveralls.io/repos/github/nette/php-generator/badge.svg?branch=master&v=1)](https://coveralls.io/github/nette/php-generator?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/php-generator/v/stable)](https://github.com/nette/php-generator/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/php-generator/blob/master/license.md)


Introduction
------------

Generate PHP code, classes, namespaces etc. with a simple programmatical API.

Documentation can be found on the [website](https://doc.nette.org/php-generator).

If you like Nette, **[please make a donation now](https://nette.org/donate)**. Thank you!


Installation
------------

The recommended way to install is via Composer:

```
composer require nette/php-generator
```

- v3.2 requires PHP 7.1 or newer (is compatible up to 7.3)
- v3.1 requires PHP 7.1 or newer (is compatible up to 7.3)
- v3.0 requires PHP 7.0 or newer (is compatible up to 7.3)
- v2.6 requires PHP 5.6 or newer (is compatible up to 7.3)


Usage
-----

Usage is very easy. Let's start with a straightforward example of generating class:

```php
$class = new Nette\PhpGenerator\ClassType('Demo');

$class
	->setFinal()
	->setExtends('ParentClass')
	->addImplement('Countable')
	->addTrait('Nette\SmartObject')
	->addComment("Description of class.\nSecond line\n")
	->addComment('@property-read Nette\Forms\Form $form');

// to generate PHP code simply cast to string or use echo:
echo $class;
```

It will render this result:

```php
/**
 * Description of class.
 * Second line
 *
 * @property-read Nette\Forms\Form $form
 */
final class Demo extends ParentClass implements Countable
{
	use Nette\SmartObject;
}
```

We can add constants and properties:

```php
$class->addConstant('ID', 123);

$class->addProperty('items', [1, 2, 3])
	->setVisibility('private')
	->setStatic()
	->addComment('@var int[]');
```

It generates:

```php
	const ID = 123;

	/** @var int[] */
	private static $items = [1, 2, 3];
```

And we can add methods with parameters:

```php
$method = $class->addMethod('count')
	->addComment('Count it.')
	->addComment('@return int')
	->setFinal()
	->setVisibility('protected')
	->setBody('return count($items ?: $this->items);');

$method->addParameter('items', []) // $items = []
		->setReference() // &$items = []
		->setTypeHint('array'); // array &$items = []
```

It results in:

```php
	/**
	 * Count it.
	 * @return int
	 */
	final protected function count(array &$items = [])
	{
		return count($items ?: $this->items);
	}
```

If the property, constant, method or parameter already exist, it will be overwritten.

Members can be removed using `removeProperty()`, `removeConstant()`, `removeMethod()` or `removeParameter()`.

PHP Generator supports all new PHP 7.3 features:

```php
$class = new Nette\PhpGenerator\ClassType('Demo');

$class->addConstant('ID', 123)
	->setVisibility('private'); // constant visiblity

$method = $class->addMethod('getValue')
	->setReturnType('int') // method return type
	->setReturnNullable() // nullable return type
	->setBody('return count($this->items);');

$method->addParameter('id')
		->setTypeHint('int') // scalar type hint
		->setNullable(); // nullable type hint

echo $class;
```

Result:

```php
class Demo
{
	private const ID = 123;

	public function getValue(?int $id): ?int
	{
		return count($this->items);
	}
}
```

You can also add existing `Method`, `Property` or `Constant` objects to the class:

```php
$method = new Nette\PhpGenerator\Method('getHandle');
$property = new Nette\PhpGenerator\Property('handle');
$const = new Nette\PhpGenerator\Constant('ROLE');

$class = (new Nette\PhpGenerator\ClassType('Demo'))
	->addMember($method)
	->addMember($property)
	->addMember($const);
```

You can clone existing methods, properties and constants with a different name using `cloneWithName()`:

```php
$methodCount = $class->getMethod('count');
$methodRecount = $methodCount->cloneWithName('recount');
$class->addMember($methodRecount);
```

Tabs versus spaces
------------------

The generated code uses tabs for indentation. If you want to have the output compatible with PSR-2 or PSR-12, use `PsrPrinter`:

```php
$printer = new Nette\PhpGenerator\PsrPrinter;

$class = new Nette\PhpGenerator\ClassType('Demo');
// ...

echo $printer->printClass($class); // 4 spaces indentation
```

It can be used also for functions, closures, namespaces etc.


Literals
--------

You can pass any PHP code to property or parameter default values via `PhpLiteral`:

```php
use Nette\PhpGenerator\PhpLiteral;

$class = new Nette\PhpGenerator\ClassType('Demo');

$class->addProperty('foo', new PhpLiteral('Iterator::SELF_FIRST'));

$class->addMethod('bar')
	->addParameter('id', new PhpLiteral('1 + 2'));

echo $class;
```

Result:

```php
class Demo
{
	public $foo = Iterator::SELF_FIRST;

	public function bar($id = 1 + 2)
	{
	}
}
```

Interface or Trait
------------------

```php
$class = new Nette\PhpGenerator\ClassType('DemoInterface');
$class->setType('interface');
// or $class->setType('trait');
```

Trait Resolutions and Visibility
--------------------------------

```php
$class = new Nette\PhpGenerator\ClassType('Demo');
$class->addTrait('SmartObject', ['sayHello as protected']);
echo $class;
```

Result:

```php
class Demo
{
	use SmartObject {
		sayHello as protected;
	}
}
```

Anonymous Class
---------------

```php
$class = new Nette\PhpGenerator\ClassType(null);
$class->addMethod('__construct')
	->addParameter('foo');

echo '$obj = new class ($val) ' . $class . ';';
```

Result:

```php
$obj = new class ($val) {

	public function __construct($foo)
	{
	}
};
```

Global Function
---------------

Code of function:

```php
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->setBody('return $a + $b;');
$function->addParameter('a');
$function->addParameter('b');
echo $function;

// or use PsrPrinter for output compatible with PSR-2 / PSR-12
// echo (new Nette\PhpGenerator\PsrPrinter)->printFunction($function);
```

Result:

```php
function foo($a, $b)
{
	return $a + $b;
}
```

Closure
-------

Code of closure:

```php
$closure = new Nette\PhpGenerator\Closure;
$closure->setBody('return $a + $b;');
$closure->addParameter('a');
$closure->addParameter('b');
$closure->addUse('c')
	->setReference();
echo $closure;

// or use PsrPrinter for output compatible with PSR-2 / PSR-12
// echo (new Nette\PhpGenerator\PsrPrinter)->printClosure($closure);
```

Result:

```php
function ($a, $b) use (&$c) {
	return $a + $b;
}
```

Method and Function Body Generator
----------------------------------

You can use special placeholders for handy way to generate method or function body.

Simple placeholders:

```php
$str = 'any string';
$num = 3;
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->addBody('return strlen(?, ?);', [$str, $num]);
echo $function;
```

Result:

```php
function foo()
{
	return strlen('any string', 3);
}
```

Variadic placeholder:

```php
$items = [1, 2, 3];
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->setBody('myfunc(...?);', [$items]);
echo $function;
```

Result:

```php
function foo()
{
	myfunc(1, 2, 3);
}
```

Escape placeholder using slash:

```php
$num = 3;
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->addParameter('a');
$function->addBody('return $a \? 10 : ?;', [$num]);
echo $function;
```

Result:

```php
function foo($a)
{
	return $a ? 10 : 3;
}
```

Namespace
---------

Classes, traits and interfaces (hereinafter classes) can be grouped into namespaces:

```php
$namespace = new Nette\PhpGenerator\PhpNamespace('Foo');

$class = $namespace->addClass('Task');
$interface = $namespace->addInterface('Countable');
$trait = $namespace->addTrait('NameAware');

// or
$class = new Nette\PhpGenerator\ClassType('Task');
$namespace->add($class);
```

If the class already exists, it will be overwritten.

You can define use-statements:

```php
$namespace->addUse('Http\Request'); // use Http\Request;
$namespace->addUse('Http\Request', 'HttpReq'); // use Http\Request as HttpReq;
```

**IMPORTANT NOTE:** when the class is part of the namespace, it is rendered slightly differently: all types (ie. type hints, return types, parent class name,
implemented interfaces and used traits) are automatically *resolved* (unless you turn it off, see below).
It means that you have to **use full class names** in definitions and they will be replaced
with aliases (according to the use-statements) or fully qualified names in the resulting code:

```php
$namespace = new Nette\PhpGenerator\PhpNamespace('Foo');
$namespace->addUse('Bar\AliasedClass');

$class = $namespace->addClass('Demo');
$class->addImplement('Foo\A') // it will resolve to A
	->addTrait('Bar\AliasedClass'); // it will resolve to AliasedClass

$method = $class->addMethod('method');
$method->addComment('@return ' . $namespace->unresolveName('Foo\D')); // in comments resolve manually
$method->addParameter('arg')
	->setTypeHint('Bar\OtherClass'); // it will resolve to \Bar\OtherClass

echo $namespace;

// or use PsrPrinter for output compatible with PSR-2 / PSR-12
// echo (new Nette\PhpGenerator\PsrPrinter)->printNamespace($namespace);
```

Result:

```php
namespace Foo;

use Bar\AliasedClass;

class Demo implements A
{
	use AliasedClass;

	/**
	 * @return D
	 */
	public function method(\Bar\OtherClass $arg)
	{
	}
}
```

Auto-resolving can be turned off this way:

```php
$printer = new Nette\PhpGenerator\Printer; // or PsrPrinter
$printer->setTypeResolving(false);
echo $printer->printNamespace($namespace);
```

PHP Files
---------

PHP files can contains multiple classes, namespaces and comments:

```php
$file = new Nette\PhpGenerator\PhpFile;
$file->addComment('This file is auto-generated.');
$file->setStrictTypes(); // adds declare(strict_types=1)

$namespace = $file->addNamespace('Foo');
$class = $namespace->addClass('A');
$class->addMethod('hello');

echo $file;

// or use PsrPrinter for output compatible with PSR-2 / PSR-12
// echo (new Nette\PhpGenerator\PsrPrinter)->printFile($file);
```

Result:

```php
<?php

/**
 * This file is auto-generated.
 */

declare(strict_types=1);

namespace Foo;

class A
{
	public function hello()
	{
	}
}
```

Generate using Reflection
-------------------------

Another common use case is to create class or method based on existing ones:

```php
$class = Nette\PhpGenerator\ClassType::from(PDO::class);

$function = Nette\PhpGenerator\GlobalFunction::from('trim');

$closure = Nette\PhpGenerator\Closure::from(
	function (stdClass $a, $b = null) {}
);
```
