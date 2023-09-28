Nette PHP Generator [![Latest Stable Version](https://poser.pugx.org/nette/php-generator/v/stable)](https://github.com/nette/php-generator/releases) [![Downloads this Month](https://img.shields.io/packagist/dm/nette/php-generator.svg)](https://packagist.org/packages/nette/php-generator)
===================

✅ Need to generate PHP code for [classes](#classes), [functions](#global-function), [PHP files](#php-files), etc.?<br>
✅ Supports all the latest PHP features like [enums](#enums), [attributes](#attributes), etc.<br>
✅ Allows you to easily modify [existing classes](#generating-according-to-existing-ones)<br>
✅ [PSR-12 compliant](#printers-and-psr-compliance) output<br>
✅ Highly mature, stable, and widely used library


Installation
------------

```shell
composer require nette/php-generator
```

For PHP compatibility, see the [table](#compatibility-table). Documentation even for older versions can be found on the [library's website](https://doc.nette.org/php-generator).


[Support Me](https://github.com/sponsors/dg)
--------------------------------------------

Do you like PHP Generator? Are you looking forward to the new features?

[![Buy me a coffee](https://files.nette.org/icons/donation-3.svg)](https://github.com/sponsors/dg)

Thank you!


Classes
-------

Let's start with a straightforward example of generating class using [ClassType](https://api.nette.org/php-generator/master/Nette/PhpGenerator/ClassType.html):

```php
$class = new Nette\PhpGenerator\ClassType('Demo');

$class
	->setFinal()
	->setExtends(ParentClass::class)
	->addImplement(Countable::class)
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
}
```

We can also use a printer to generate the code, which, unlike `echo $class`, we will be able to [further configure](#printers-and-psr-compliance):

```php
$printer = new Nette\PhpGenerator\Printer;
echo $printer->printClass($class);
```

We can add constants (class [Constant](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Constant.html)) and properties (class [Property](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Property.html)):

```php
$class->addConstant('ID', 123)
	->setProtected() // constant visiblity
	->setType('int')
	->setFinal();

$class->addProperty('items', [1, 2, 3])
	->setPrivate() // or setVisibility('private')
	->setStatic()
	->addComment('@var int[]');

$class->addProperty('list')
	->setType('?array')
	->setInitialized(); // prints '= null'
```

It generates:

```php
final protected const int ID = 123;

/** @var int[] */
private static $items = [1, 2, 3];

public ?array $list = null;
```

And we can add [methods](#Method-and-Function-Signature):

```php
$method = $class->addMethod('count')
	->addComment('Count it.')
	->setFinal()
	->setProtected()
	->setReturnType('?int') // method return type
	->setBody('return count($items ?: $this->items);');

$method->addParameter('items', []) // $items = []
	->setReference()           // &$items = []
	->setType('array');        // array &$items = []
```

It results in:

```php
/**
 * Count it.
 */
final protected function count(array &$items = []): ?int
{
	return count($items ?: $this->items);
}
```

Promoted parameters introduced by PHP 8.0 can be passed to the constructor:

```php
$method = $class->addMethod('__construct');
$method->addPromotedParameter('name');
$method->addPromotedParameter('args', [])
	->setPrivate();
```

It results in:

```php
public function __construct(
	public $name,
	private $args = [],
) {
}
```

Readonly properties and classes can be marked via `setReadOnly()`.

------

If the added property, constant, method or parameter already exist, it throws exception.

Members can be removed using `removeProperty()`, `removeConstant()`, `removeMethod()` or `removeParameter()`.

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


Interface or Trait
------------------

You can create interfaces and traits (classes [InterfaceType](https://api.nette.org/php-generator/master/Nette/PhpGenerator/InterfaceType.html) and [TraitType](https://api.nette.org/php-generator/master/Nette/PhpGenerator/TraitType.html)):

```php
$interface = new Nette\PhpGenerator\InterfaceType('MyInterface');
$trait = new Nette\PhpGenerator\TraitType('MyTrait');
```

Using traits:

```php
$class = new Nette\PhpGenerator\ClassType('Demo');
$class->addTrait('SmartObject');
$class->addTrait('MyTrait')
	->addResolution('sayHello as protected')
	->addComment('@use MyTrait<Foo>');
echo $class;
```

Result:

```php
class Demo
{
	use SmartObject;
	/** @use MyTrait<Foo> */
	use MyTrait {
		sayHello as protected;
	}
}
```


Enums
-----

You can easily create the enums that PHP 8.1 brings (class [EnumType](https://api.nette.org/php-generator/master/Nette/PhpGenerator/EnumType.html)):

```php
$enum = new Nette\PhpGenerator\EnumType('Suit');
$enum->addCase('Clubs');
$enum->addCase('Diamonds');
$enum->addCase('Hearts');
$enum->addCase('Spades');

echo $enum;
```

Result:

```php
enum Suit
{
	case Clubs;
	case Diamonds;
	case Hearts;
	case Spades;
}
```

You can also define scalar equivalents for cases to create a backed enum:

```php
$enum->addCase('Clubs', '♣');
$enum->addCase('Diamonds', '♦');
```

It is possible to add a comment or [attributes](#attributes) to each case using `addComment()` or `addAttribute()`.


Anonymous Class
---------------

Give `null` as the name and you have an anonymous class:

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

Code of functions will generate class [GlobalFunction](https://api.nette.org/php-generator/master/Nette/PhpGenerator/GlobalFunction.html):

```php
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->setBody('return $a + $b;');
$function->addParameter('a');
$function->addParameter('b');
echo $function;

// or use PsrPrinter for output conforming to PSR-2 / PSR-12 / PER
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

Code of closures will generate class [Closure](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Closure.html):

```php
$closure = new Nette\PhpGenerator\Closure;
$closure->setBody('return $a + $b;');
$closure->addParameter('a');
$closure->addParameter('b');
$closure->addUse('c')
	->setReference();
echo $closure;

// or use PsrPrinter for output conforming to PSR-2 / PSR-12 / PER
// echo (new Nette\PhpGenerator\PsrPrinter)->printClosure($closure);
```

Result:

```php
function ($a, $b) use (&$c) {
	return $a + $b;
}
```


Arrow Function
--------------

You can also print closure as arrow function using printer:

```php
$closure = new Nette\PhpGenerator\Closure;
$closure->setBody('$a + $b');
$closure->addParameter('a');
$closure->addParameter('b');

echo (new Nette\PhpGenerator\Printer)->printArrowFunction($closure);
```

Result:

```php
fn($a, $b) => $a + $b
```


Method and Function Signature
-----------------------------

Methods are represented by the class [Method](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Method.html). You can set visibility, return value, add comments, [attributes|#Attributes] etc:

```php
$method = $class->addMethod('count')
	->addComment('Count it.')
	->setFinal()
	->setProtected()
	->setReturnType('?int');
```

Each parameter is represented by a class [Parameter](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Parameter.html). Again, you can set every conceivable property:

```php
$method->addParameter('items', []) // $items = []
	->setReference() // &$items = []
	->setType('array'); // array &$items = []

// function count(&$items = [])
```

To define the so-called variadics parameters (or also the splat, spread, ellipsis, unpacking or three dots operator), use `setVariadics()`:

```php
$method = $class->addMethod('count');
$method->setVariadics(true);
$method->addParameter('items');
```

Generates:

```php
function count(...$items)
{
}
```


Method and Function Body
------------------------

The body can be passed to the `setBody()` method at once or sequentially (line by line) by repeatedly calling `addBody()`:

```php
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->addBody('$a = rand(10, 20);');
$function->addBody('return $a;');
echo $function;
```

Result

```php
function foo()
{
	$a = rand(10, 20);
	return $a;
}
```

You can use special placeholders for handy way to inject variables.

Simple placeholders `?`

```php
$str = 'any string';
$num = 3;
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->addBody('return substr(?, ?);', [$str, $num]);
echo $function;
```

Result:

```php
function foo()
{
	return substr('any string', 3);
}
```

Variadic placeholder `...?`

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

You can also use PHP 8 named parameters using placeholder `...?:`

```php
$items = ['foo' => 1, 'bar' => true];
$function->setBody('myfunc(...?:);', [$items]);

// myfunc(foo: 1, bar: true);
```

Escape placeholder using slash `\?`

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


Printers and PSR Compliance
---------------------------

The [Printer](https://api.nette.org/php-generator/master/Nette/PhpGenerator/Printer.html) class is used to generate PHP code:

```php
$class = new Nette\PhpGenerator\ClassType('Demo');
// ...

$printer = new Nette\PhpGenerator\Printer;
echo $printer->printClass($class); // same as: echo $class
```

It can generate code for all other elements, offering methods such as `printFunction()`, `printNamespace()`, etc.

Additionally, the `PsrPrinter` class is available, whose output is in compliance with the PSR-2 / PSR-12 / PER coding style:

```php
$printer = new Nette\PhpGenerator\PsrPrinter;
echo $printer->printClass($class);
```

Need to fine-tune behavior to your needs? Create your own printer by inheriting from the `Printer` class. You can reconfigure these variables:

```php
class MyPrinter extends Nette\PhpGenerator\Printer
{
	// length of the line after which the line will break
	public int $wrapLength = 120;
	// indentation character, can be replaced with a sequence of spaces
	public string $indentation = "\t";
	// number of blank lines between properties
	public int $linesBetweenProperties = 0;
	// number of blank lines between methods
	public int $linesBetweenMethods = 2;
	// number of blank lines between groups of use statements for classes, functions, and constants
	public int $linesBetweenUseTypes = 0;
	// position of the opening brace for functions and methods
	public bool $bracesOnNextLine = true;
	// place one parameter in one line, even if it has an attribute or is promoted
	public bool $singleParameterOnOneLine = false;
	// separator between the right parenthesis and return type of functions and methods
	public string $returnTypeColon = ': ';
}
```

How and why exactly does the standard `Printer` and `PsrPrinter` differ? Why isn't there just one printer, the `PsrPrinter`, in the package?

The standard `Printer` formats the code as we do it in all of Nette. Since Nette was created much earlier than PSR, and also because PSR for many years did not deliver standards in time, but sometimes even with several years of delay from the introduction of a new feature in PHP, this resulted in a few minor differences in the coding standard.
The bigger difference is just the use of tabs instead of spaces. We know that by using tabs in our projects we allow for width adjustment, which is [essential for people with visual impairments](https://doc.nette.org/en/contributing/coding-standard#toc-tabs-instead-of-spaces).
An example of a minor difference is the placement of the curly brace on a separate line for functions and methods and always. We see the PSR recommendation as illogical and [leading to a decrease in code clarity](https://doc.nette.org/en/contributing/coding-standard#toc-wrapping-and-braces).


Types
-----

Each type or union/intersection type can be passed as a string, you can also use predefined constants for native types:

```php
use Nette\PhpGenerator\Type;

$member->setType('array'); // or Type::Array;
$member->setType('?array'); // or Type::nullable(Type::Array);
$member->setType('array|string'); // or Type::union(Type::Array, Type::String)
$member->setType('array|string|null'); // or Type::nullable(Type::union(Type::Array, Type::String))
$member->setType('Foo&Bar'); // or Type::intersection(Foo::class, Bar::class)
$member->setType(null); // removes type
```

The same applies to the method `setReturnType()`.


Literals
--------

With `Literal` you can pass arbitrary PHP code to, for example, default property or parameter values etc:

```php
use Nette\PhpGenerator\Literal;

$class = new Nette\PhpGenerator\ClassType('Demo');

$class->addProperty('foo', new Literal('Iterator::SELF_FIRST'));

$class->addMethod('bar')
	->addParameter('id', new Literal('1 + 2'));

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

You can also pass parameters to `Literal` and have it formatted into valid PHP code using [special placeholders](#method-and-function-body-generator):

```php
new Literal('substr(?, ?)', [$a, $b]);
// generates, for example: substr('hello', 5);
```

The literal representing the creation of a new object is easily generated by the `new` method:

```php
Literal::new(Demo::class, [$a, 'foo' => $b]);
// generates, for example: new Demo(10, foo: 20)
```


Attributes
----------

You can add PHP 8 attributes to all classes, methods, properties, constants, enum cases, functions, closures and parameters. [Literals](#literals) can also be used as parameter values.

```php
$class = new Nette\PhpGenerator\ClassType('Demo');
$class->addAttribute('Table', [
	'name' => 'user',
	'constraints' => [
		Literal::new('UniqueConstraint', ['name' => 'ean', 'columns' => ['ean']]),
	],
]);

$class->addProperty('list')
	->addAttribute('Deprecated');

$method = $class->addMethod('count')
	->addAttribute('Foo\Cached', ['mode' => true]);

$method->addParameter('items')
	->addAttribute('Bar');

echo $class;
```

Result:

```php
#[Table(name: 'user', constraints: [new UniqueConstraint(name: 'ean', columns: ['ean'])])]
class Demo
{
	#[Deprecated]
	public $list;


	#[Foo\Cached(mode: true)]
	public function count(
		#[Bar]
		$items,
	) {
	}
}
```


Namespace
---------

Classes, traits, interfaces and enums (hereinafter classes) can be grouped into namespaces (class [PhpNamespace](https://api.nette.org/php-generator/master/Nette/PhpGenerator/PhpNamespace.html)):

```php
$namespace = new Nette\PhpGenerator\PhpNamespace('Foo');

// create new classes in the namespace
$class = $namespace->addClass('Task');
$interface = $namespace->addInterface('Countable');
$trait = $namespace->addTrait('NameAware');

// or insert an existing class into the namespace
$class = new Nette\PhpGenerator\ClassType('Task');
$namespace->add($class);
```

If the class already exists, it throws exception.

You can define use-statements:

```php
// use Http\Request;
$namespace->addUse(Http\Request::class);
// use Http\Request as HttpReq;
$namespace->addUse(Http\Request::class, 'HttpReq');
// use function iter\range;
$namespace->addUseFunction('iter\range');
```

To simplify a fully qualified class, function or constant name according to the defined aliases, use the `simplifyName` method:

```php
echo $namespace->simplifyName('Foo\Bar'); // 'Bar', because 'Foo' is current namespace
echo $namespace->simplifyName('iter\range', $namespace::NameFunction); // 'range', because of the defined use-statement
```

Conversely, you can convert a simplified class, function or constant name to a fully qualified one using the `resolveName` method:

```php
echo $namespace->resolveName('Bar'); // 'Foo\Bar'
echo $namespace->resolveName('range', $namespace::NameFunction); // 'iter\range'
```


Class Names Resolving
---------------------

**When the class is part of the namespace, it is rendered slightly differently**: all types (ie. type hints, return types, parent class name,
implemented interfaces, used traits and attributes) are automatically *resolved* (unless you turn it off, see below).
It means that you have to **use full class names** in definitions and they will be replaced with aliases (according to the use-statements) or fully qualified names in the resulting code:

```php
$namespace = new Nette\PhpGenerator\PhpNamespace('Foo');
$namespace->addUse('Bar\AliasedClass');

$class = $namespace->addClass('Demo');
$class->addImplement('Foo\A') // it will simplify to A
	->addTrait('Bar\AliasedClass'); // it will simplify to AliasedClass

$method = $class->addMethod('method');
$method->addComment('@return ' . $namespace->simplifyType('Foo\D')); // in comments simplify manually
$method->addParameter('arg')
	->setType('Bar\OtherClass'); // it will resolve to \Bar\OtherClass

echo $namespace;

// or use PsrPrinter for output conforming to PSR-2 / PSR-12 / PER
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

Classes, functions and namespaces can be grouped into PHP files represented by the class [PhpFile](https://api.nette.org/php-generator/master/Nette/PhpGenerator/PhpFile.html):

```php
$file = new Nette\PhpGenerator\PhpFile;
$file->addComment('This file is auto-generated.');
$file->setStrictTypes(); // adds declare(strict_types=1)

$class = $file->addClass('Foo\A');
$function = $file->addFunction('Foo\foo');

// or
// $namespace = $file->addNamespace('Foo');
// $class = $namespace->addClass('A');
// $function = $namespace->addFunction('foo');

echo $file;

// or use PsrPrinter for output conforming to PSR-2 / PSR-12 / PER
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
}

function foo()
{
}
```


Generating According to Existing Ones
-------------------------------------

In addition to being able to model classes and functions using the API described above, you can also have them automatically generated using existing ones:

```php
// creates a class identical to the PDO class
$class = Nette\PhpGenerator\ClassType::from(PDO::class);

// creates a function identical to trim()
$function = Nette\PhpGenerator\GlobalFunction::from('trim');

// creates a closure as specified
$closure = Nette\PhpGenerator\Closure::from(
	function (stdClass $a, $b = null) {},
);
```

Function and method bodies are empty by default. If you want to load them as well, use this way
(it requires `nikic/php-parser` to be installed):

```php
$class = Nette\PhpGenerator\ClassType::from(Foo::class, withBodies: true);

$function = Nette\PhpGenerator\GlobalFunction::from('foo', withBody: true);
```


Loading from PHP File
---------------------

You can also load functions, classes, interfaces and enums directly from a string of PHP code. For example, we create `ClassType` object this way:

```php
$class = Nette\PhpGenerator\ClassType::fromCode(<<<XX
	<?php

	class Demo
	{
		public $foo;
	}
	XX);
```

When loading classes from PHP code, single line comments outside of method bodies are ignored (e.g. for properties, etc.) because this library does not have an API to work with them.

You can also load the entire PHP file directly, which can contain any number of classes, functions or even multiple namespaces:

```php
$file = Nette\PhpGenerator\PhpFile::fromCode(file_get_contents('classes.php'));
```

The initial file comment and the `strict_types` declaration are also loaded. On the other hand, all other global code is ignored.

This requires `nikic/php-parser` to be installed.

*(If you need to manipulate global code in files or individual statements in method bodies, it is better to use the `nikic/php-parser` library directly.)*


Variables Dumper
----------------

The Dumper returns a parsable PHP string representation of a variable. Provides better and clearer output that native function `var_export()`.

```php
$dumper = new Nette\PhpGenerator\Dumper;

$var = ['a', 'b', 123];

echo $dumper->dump($var); // prints ['a', 'b', 123]
```


Compatibility Table
-------------------

- PhpGenerator 4.1 is compatible with PHP 8.0 to 8.3
- PhpGenerator 4.0 is compatible with PHP 8.0 to 8.3
- PhpGenerator 3.6 is compatible with PHP 7.2 to 8.2
- PhpGenerator 3.2 – 3.5 is compatible with PHP 7.1 to 8.0
- PhpGenerator 3.1 is compatible with PHP 7.1 to 7.3
- PhpGenerator 3.0 is compatible with PHP 7.0 to 7.3
- PhpGenerator 2.6 is compatible with PHP 5.6 to 7.3
