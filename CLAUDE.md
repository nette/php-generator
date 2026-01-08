# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Nette PHP Generator is a library for programmatically generating PHP code - classes, functions, namespaces, and complete PHP files. It supports all modern PHP features including property hooks (PHP 8.4), enums, attributes, asymmetric visibility, and more.

The library provides both a builder API for constructing PHP structures and extraction capabilities for loading existing PHP code.

## Essential Commands

### Testing
```bash
# Run all tests
composer run tester
# OR
vendor/bin/tester tests -s

# Run specific test file
vendor/bin/tester tests/PhpGenerator/ClassType.phpt -s

# Run tests in specific directory
vendor/bin/tester tests/PhpGenerator/ -s
```

### Static Analysis
```bash
# Run PHPStan analysis
composer run phpstan
# OR
vendor/bin/phpstan analyse
```

### Code Style
The project follows Nette Coding Standard with configuration in `ncs.php`. Uses tabs for indentation and braces on next line for functions/methods.

## Architecture

### Core Components

**PhpFile** (`PhpFile.php`)
- Top-level container representing a complete PHP file
- Manages namespaces, strict_types declaration, and file-level comments
- Entry point: `PhpFile::add()` for adding namespaces/classes/functions

**PhpNamespace** (`PhpNamespace.php`)
- Represents a namespace with use statements and contained classes/functions
- Handles type resolution and name simplification based on use statements
- Methods: `addClass()`, `addInterface()`, `addTrait()`, `addEnum()`, `addFunction()`

**ClassLike** (`ClassLike.php`)
- Abstract base for class-like structures (classes, interfaces, traits, enums)
- Provides common functionality for attributes and comments
- Extended by ClassType, InterfaceType, TraitType, EnumType

**ClassType** (`ClassType.php`)
- Represents class definitions with full feature support
- Composes traits for properties, methods, constants, and trait usage
- Supports final, abstract, readonly modifiers, extends, and implements

**Method** (`Method.php`) & **GlobalFunction** (`GlobalFunction.php`)
- Method represents class/interface/trait methods
- GlobalFunction represents standalone functions
- Both use FunctionLike trait for parameters, return types, and body

**Property** (`Property.php`)
- Represents class properties with type hints, visibility, hooks
- Supports PHP 8.4 property hooks (get/set) via PropertyHook class
- Supports asymmetric visibility (different get/set visibility)

**Printer** (`Printer.php`) & **PsrPrinter** (`PsrPrinter.php`)
- Printer: Generates code following Nette Coding Standard (tabs, braces on next line)
- PsrPrinter: Generates PSR-2/PSR-12/PER compliant code
- Configurable via properties: `$wrapLength`, `$indentation`, `$linesBetweenMethods`, etc.
- Handles type resolution when printing within namespace context

**Factory** (`Factory.php`) & **Extractor** (`Extractor.php`)
- Factory: Creates PhpGenerator objects from existing classes/functions
- Extractor: Low-level parser integration (requires nikic/php-parser)
- Enable loading existing code: `ClassType::from()`, `PhpFile::fromCode()`

**Dumper** (`Dumper.php`)
- Converts PHP values to code representation
- Used internally for default values, but also available for standalone use
- Better output than `var_export()`

### Trait Organization (`Traits/`)

Shared functionality is implemented via traits for composition:

- **FunctionLike**: Parameters, return types, body management for functions/methods
- **PropertyLike**: Core property functionality (type, value, visibility)
- **AttributeAware**: Attribute support for all elements
- **CommentAware**: Doc comment management
- **VisibilityAware**: Visibility modifiers (public/protected/private)
- **ConstantsAware**: Class constant management
- **MethodsAware**: Method collection management
- **PropertiesAware**: Property collection management
- **TraitsAware**: Trait usage management

This trait-based architecture allows ClassType to compose all necessary features while keeping concerns separated.

### Type System

**Type** (`Type.php`)
- Constants for native types (String, Int, Array, etc.)
- Helper methods for union/intersection/nullable types
- Used throughout for type hints and return types

**Literal** (`Literal.php`)
- Represents raw PHP code that should not be escaped
- Used for default values, constants, expressions
- Supports placeholders for value injection (see Placeholders section below)
- `Literal::new()` helper for creating object instantiation literals

### Test Structure

Tests use Nette Tester with `.phpt` extension:
- Located in `tests/PhpGenerator/`
- Mirror source file organization
- Use `test()` function for test cases (defined in `bootstrap.php`)
- Use `testException()` for exception testing
- Helper functions: `same()`, `sameFile()` for assertions

Example test pattern:
```php
test('description of what is being tested', function () {
    $class = new ClassType('Demo');
    // ... test code
    Assert::same($expected, (string) $class);
});
```

## Important Features

### Placeholders in Function Bodies

The library supports special placeholders for inserting values into method/function bodies:

- **`?`** - Simple placeholder for single values (strings, numbers, arrays)
  ```php
  $function->addBody('return substr(?, ?);', [$str, $num]);
  // Generates: return substr('any string', 3);
  ```

- **`...?`** - Variadic placeholder (unpacks arrays as separate arguments)
  ```php
  $function->setBody('myfunc(...?);', [[1, 2, 3]]);
  // Generates: myfunc(1, 2, 3);
  ```

- **`...?:`** - Named parameters placeholder for PHP 8+
  ```php
  $function->setBody('myfunc(...?:);', [['foo' => 1, 'bar' => true]]);
  // Generates: myfunc(foo: 1, bar: true);
  ```

- **`\?`** - Escaped placeholder (literal question mark)
  ```php
  $function->addBody('return $a \? 10 : ?;', [$num]);
  // Generates: return $a ? 10 : 3;
  ```

### Printer Configuration

Both `Printer` and `PsrPrinter` can be customized by extending and overriding public properties:

```php
class MyPrinter extends Nette\PhpGenerator\Printer
{
    public int $wrapLength = 120;              // line length for wrapping
    public string $indentation = "\t";          // indentation character
    public int $linesBetweenProperties = 0;     // blank lines between properties
    public int $linesBetweenMethods = 2;        // blank lines between methods
    public int $linesBetweenUseTypes = 0;       // blank lines between use statement groups
    public bool $bracesOnNextLine = true;       // opening brace position for functions/methods
    public bool $singleParameterOnOneLine = false; // single parameter formatting
    public bool $omitEmptyNamespaces = true;    // omit empty namespaces
    public string $returnTypeColon = ': ';      // separator before return type
}
```

**Note:** `Printer` uses Nette Coding Standard (tabs, braces on next line), while `PsrPrinter` follows PSR-2/PSR-12/PER (spaces, braces on same line).

### Property Hooks (PHP 8.4)

Property hooks allow defining get/set operations directly on properties:

```php
$prop = $class->addProperty('firstName')->setType('string');
$prop->addHook('set', 'strtolower($value)')  // Arrow function style
    ->addParameter('value')->setType('string');
$prop->addHook('get')                         // Block style
    ->setBody('return ucfirst($this->firstName);');
```

Property hooks can be marked as `abstract` or `final` using `setAbstract()` / `setFinal()`.

### Asymmetric Visibility (PHP 8.4)

Properties can have different visibility for reading vs writing:

```php
// Using setVisibility() with two parameters
$class->addProperty('name')
    ->setVisibility('public', 'private'); // public get, private set

// Using modifier methods with 'get' or 'set' mode
$class->addProperty('id')
    ->setProtected('set'); // protected set, public get (default)
```

Generates: `public private(set) string $name;`

### ClassManipulator

The `ClassManipulator` class provides advanced class manipulation:

- **`inheritMethod($name)`** - Copies method from parent/interface for overriding
- **`inheritProperty($name)`** - Copies property from parent class
- **`implement($interface)`** - Automatically implements all methods/properties from interface/abstract class

```php
$manipulator = new Nette\PhpGenerator\ClassManipulator($class);
$manipulator->implement(SomeInterface::class);
// Now $class contains stub implementations of all interface methods
```

### Arrow Functions

While `Closure` represents anonymous functions, you can generate arrow functions using the printer:

```php
$closure = new Nette\PhpGenerator\Closure;
$closure->setBody('$a + $b');  // Note: arrow function body without 'return'
$closure->addParameter('a');
$closure->addParameter('b');

echo (new Nette\PhpGenerator\Printer)->printArrowFunction($closure);
// Generates: fn($a, $b) => $a + $b
```

### Cloning Members

Methods, properties, and constants can be cloned under a different name:

```php
$methodCount = $class->getMethod('count');
$methodRecount = $methodCount->cloneWithName('recount');
$class->addMember($methodRecount);
```

## Key Patterns

### Builder Pattern
All classes use fluent interface - methods return `$this`/`static` for chaining:
```php
$class->setFinal()
    ->setExtends(ParentClass::class)
    ->addImplement(Countable::class);
```

### Type Resolution
When a class is part of a namespace, types are automatically resolved:
- Fully qualified names → simplified based on use statements
- Can be disabled: `$printer->setTypeResolving(false)`
- Use `$namespace->simplifyType()` for manual resolution

### Code Generation Flow
1. Create PhpFile
2. Add namespace(s)
3. Add classes/interfaces/traits/enums/functions to namespace
4. Add members (properties/methods/constants) to classes
5. Convert to string or use Printer explicitly

### Validation
Classes validate themselves before printing via `validate()` method. Throws `Nette\InvalidStateException` for invalid states (e.g., abstract and final simultaneously).

### Cloning
All major classes implement `__clone()` to deep-clone contained objects (methods, properties, etc.).

## Common Tasks

### Adding New Features
1. Check if feature needs new class or extends existing (e.g., PropertyHook for property hooks)
2. Add tests first in `tests/PhpGenerator/`
3. Update Printer to generate correct syntax
4. Update Factory/Extractor if feature should be loadable from existing code
5. Consider PsrPrinter compatibility

### Updating for New PHP Versions
1. Add support to builder classes (e.g., new method/property)
2. Update Printer with syntax generation
3. Update Extractor to parse the feature (via php-parser)
4. Add comprehensive tests including edge cases
5. Update README.md with examples

### Test Expectations
- Test files may have corresponding `.expect` files with expected output
- Use `sameFile()` helper to compare against expectation files
- This keeps test files clean and makes output changes visible in diffs

## Important Notes & Limitations

### Loading from Existing Code
- **Requires `nikic/php-parser`** to load method/function bodies with `withBodies: true` / `withBody: true`
- Without php-parser, bodies are empty but signatures are complete
- Single-line comments outside method bodies are ignored when loading (library has no API for them)
- Use `nikic/php-parser` directly if you need to manipulate global code or individual statements

### PhpFile Restrictions
- **No global code allowed** - PhpFile can only contain namespaces, classes, functions
- Cannot add arbitrary code like `echo 'hello'` outside functions/classes
- Use `setStrictTypes()` for `declare(strict_types=1)` declaration

### Exception Handling
- Adding duplicate members (same name) throws `Nette\InvalidStateException`
- Use `addMember($member, overwrite: true)` to replace existing members
- Invalid class states (e.g., abstract + final) detected by `validate()` method

### Removal Methods
- `removeProperty($name)`, `removeConstant($name)`, `removeMethod($name)`, `removeParameter($name)`
- Available on respective container classes

### Compatibility
- **PhpGenerator 4.1+** supports PHP 8.0 to 8.4
- **PhpGenerator 4.2** (current) compatible with PHP 8.1 to 8.5
- Check composer.json for exact version requirements
