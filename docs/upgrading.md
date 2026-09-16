# Upgrading nette/php-generator

## 4.2.2

- **Signature** `Nette\PhpGenerator\Printer::$dumper`: typed `Nette\PhpGenerator\Dumper` instead of `?Nette\PhpGenerator\Dumper`; affects subclasses assigning `null` to it (a2181b09)

## 4.2.1

- **Signature** `Nette\PhpGenerator\ClassLike::__construct()`: the `$namespace` parameter is no longer declared, use `Nette\PhpGenerator\ClassLike::setNamespace()`; passing it positionally still works (5800f11d)
- **Signature** `Nette\PhpGenerator\ClassType::__construct()`: the `$namespace` parameter is no longer declared, use `Nette\PhpGenerator\ClassLike::setNamespace()`; passing it positionally still works (5800f11d)
- **Signature** `$class` of `Nette\PhpGenerator\PhpNamespace::add()` is renamed to `$item` and also accepts `Nette\PhpGenerator\GlobalFunction`; affects callers using the named argument `class:` (5c54453d)
- **Changed** `Nette\PhpGenerator\PhpNamespace::add()` now calls `setNamespace()` on the added object, overwriting the namespace it carried; previously the object was left untouched (00e2a35c)

## 4.2.0

- **Removed** `Nette\PhpGenerator\ClassType::class()` (deprecated since 4.0.1): use `new Nette\PhpGenerator\ClassType($name)` (342cd2a1)
- **Removed** `Nette\PhpGenerator\ClassType::interface()` (deprecated since 4.0.1): use `new Nette\PhpGenerator\InterfaceType($name)` (342cd2a1)
- **Removed** `Nette\PhpGenerator\ClassType::trait()` (deprecated since 4.0.1): use `new Nette\PhpGenerator\TraitType($name)` (342cd2a1)
- **Removed** `Nette\PhpGenerator\ClassType::enum()` (deprecated since 4.0.1): use `new Nette\PhpGenerator\EnumType($name)` (342cd2a1)
- **Removed** `Nette\PhpGenerator\ClassType::getType()` (deprecated since 4.0.1): it always returned `'class'`, test the object with `instanceof` instead (342cd2a1)
- **Removed** `Nette\PhpGenerator\ClassManipulator::implementInterface()` (deprecated since 4.1.7): use `Nette\PhpGenerator\ClassManipulator::implement()` (342cd2a1)
- **Deprecated** the second argument of `Nette\PhpGenerator\Traits\TraitsAware::addTrait()`: call `Nette\PhpGenerator\TraitUse::addResolution()` on the returned object for each resolution; using the old name triggers `E_USER_DEPRECATED` (72329443)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VISIBILITY_PUBLIC`: use `Nette\PhpGenerator\Visibility::Public`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VISIBILITY_PROTECTED`: use `Nette\PhpGenerator\Visibility::Protected`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VISIBILITY_PRIVATE`: use `Nette\PhpGenerator\Visibility::Private`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VisibilityPublic`: use `Nette\PhpGenerator\Visibility::Public`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.10) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VisibilityProtected`: use `Nette\PhpGenerator\Visibility::Protected`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.10) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassLike::VisibilityPrivate`: use `Nette\PhpGenerator\Visibility::Private`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.10) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_CLASS`: there is no replacement; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using it triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_INTERFACE`: there is no replacement; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using it triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_TRAIT`: there is no replacement; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using it triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_ENUM`: there is no replacement; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using it triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::NAME_NORMAL`: use `Nette\PhpGenerator\PhpNamespace::NameNormal`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::NAME_FUNCTION`: use `Nette\PhpGenerator\PhpNamespace::NameFunction`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::NAME_CONSTANT`: use `Nette\PhpGenerator\PhpNamespace::NameConstant`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::STRING`: use `Nette\PhpGenerator\Type::String`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::INT`: use `Nette\PhpGenerator\Type::Int`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::FLOAT`: use `Nette\PhpGenerator\Type::Float`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::BOOL`: use `Nette\PhpGenerator\Type::Bool`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::ARRAY`: use `Nette\PhpGenerator\Type::Array`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::OBJECT`: use `Nette\PhpGenerator\Type::Object`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::CALLABLE`: use `Nette\PhpGenerator\Type::Callable`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::ITERABLE`: use `Nette\PhpGenerator\Type::Iterable`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::VOID`: use `Nette\PhpGenerator\Type::Void`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::NEVER`: use `Nette\PhpGenerator\Type::Never`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::MIXED`: use `Nette\PhpGenerator\Type::Mixed`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::FALSE`: use `Nette\PhpGenerator\Type::False`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::NULL`: use `Nette\PhpGenerator\Type::Null`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::SELF`: use `Nette\PhpGenerator\Type::Self`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::PARENT`: use `Nette\PhpGenerator\Type::Parent`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Deprecated** `Nette\PhpGenerator\Type::STATIC`: use `Nette\PhpGenerator\Type::Static`; the constant now carries `#[\Deprecated]`, so on PHP 8.4+ using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.9) (0698ed4d)
- **Changed** `Nette\PhpGenerator\Visibility` is now a native backed enum; previously a class with string constants, so `Nette\PhpGenerator\Visibility::Public` is a `Nette\PhpGenerator\Visibility` instance rather than the string `'public'` and `Nette\PhpGenerator\Visibility::from()` returns an instance rather than a validated string (4707546a)
- **Changed** `Nette\PhpGenerator\PropertyHookType` is now a native backed enum; previously a class with string constants, so its cases are `Nette\PhpGenerator\PropertyHookType` instances rather than the strings `'get'` and `'set'` (4707546a)
- **Changed** `Nette\PhpGenerator\PropertyAccessMode` is now a native backed enum; previously a class with string constants, so its cases are `Nette\PhpGenerator\PropertyAccessMode` instances rather than the strings `'get'` and `'set'` (4707546a)

## 4.1.8

- **Changed** `Nette\PhpGenerator\Factory::fromClassReflection()` called with `withBodies: true` on an interface now throws `Nette\NotSupportedException`; previously it passed silently (ff08574b)

## 4.1.7

- **Renamed** `Nette\PhpGenerator\ClassManipulator::implementInterface()` → `Nette\PhpGenerator\ClassManipulator::implement()`; using the old name triggers `E_USER_DEPRECATED` (4628decb)
- **Signature** `Nette\PhpGenerator\Property::setVisibility()`: takes `$get` and an optional `$set` instead of a single `$value`, so a property can have asymmetric visibility; affects callers using the named argument `value:` (9fa32362)
- **Signature** `$function` of `Nette\PhpGenerator\Printer::printParameters()` also accepts `Nette\PhpGenerator\PropertyHook`; affects subclasses overriding it, which must widen the type as well (5126fb87)
- **Changed** `Nette\PhpGenerator\Constant::setType()` now keeps a leading `?` and `getType()` returns it; previously the `?` was silently stripped (e45595f2)

## 4.1.6

- **Deprecated** `Nette\PhpGenerator\ClassType::inheritMethod()`: wrap the class in `new Nette\PhpGenerator\ClassManipulator($class)` and call `inheritMethod()` on it; the old name is silently deprecated (ca63f985)
- **Deprecated** `Nette\PhpGenerator\ClassType::inheritProperty()`: wrap the class in `new Nette\PhpGenerator\ClassManipulator($class)` and call `inheritProperty()` on it; the old name is silently deprecated (ca63f985)
- **Changed** `Nette\PhpGenerator\Parameter::isNullable()` now returns `true` also when the default value is `null`; previously only when `setNullable()` was called (2debfd61)
- **Changed** `Nette\PhpGenerator\Property::isNullable()` now returns `true` also when the property is initialized with `null`; previously only when `setNullable()` was called (2debfd61)

## 4.1.5

- **Signature** `$on` of `Nette\PhpGenerator\PhpFile::setStrictTypes()` is renamed to `$state`; affects callers using the named argument (8a9c4077)
- **Changed** `Nette\PhpGenerator\Method` now deep-copies its parameters when cloned; previously the clone shared the same `Nette\PhpGenerator\Parameter` instances with the original (f055a0ed)
- **Changed** `Nette\PhpGenerator\GlobalFunction` now deep-copies its parameters when cloned; previously the clone shared the same `Nette\PhpGenerator\Parameter` instances with the original (f055a0ed)
- **Changed** `Nette\PhpGenerator\Closure` now deep-copies its parameters when cloned; previously the clone shared the same `Nette\PhpGenerator\Parameter` instances with the original (f055a0ed)
- **Changed** `Nette\PhpGenerator\ClassLike` now deep-copies its attributes when cloned; previously the clone shared the same `Nette\PhpGenerator\Attribute` instances with the original (f055a0ed)

## 4.1.4

- **Changed** `Nette\PhpGenerator\ClassLike::from()` now triggers `E_USER_WARNING` when the reflected declaration cannot be represented by the subclass it was called on; previously it silently returned an instance of another subtype (af74f211)
- **Changed** `Nette\PhpGenerator\ClassLike::fromCode()` now triggers `E_USER_WARNING` when the parsed code cannot be represented by the subclass it was called on; previously it silently returned an instance of another subtype (af74f211)

## 4.1.2

- **Changed** `Nette\PhpGenerator\Type::nullable()` now appends `|null` to a union type instead of prefixing `?`, and throws `Nette\InvalidArgumentException` for an intersection type and for `'null'` or `'mixed'` with `$nullable` false (abc0e79b)

## 4.1.1

- **Deprecated** `Nette\PhpGenerator\ClassType::getType()`: it always returns `'class'`, test the object with `instanceof` instead; using it triggers `E_USER_DEPRECATED` (deprecated since 4.0.1) (047e8a63)
- **Changed** `Nette\PhpGenerator\Printer::printNamespace()` no longer prints an empty namespace; set `Nette\PhpGenerator\Printer::$omitEmptyNamespaces` to `false` to keep the previous output (bcb74f30)

## 4.1.0

- **Removed** `Nette\PhpGenerator\ClassType::$methods` (deprecated since 4.0.0): use `Nette\PhpGenerator\ClassType::getMethods()` and `setMethods()` (7326d977)
- **Removed** `Nette\PhpGenerator\ClassType::$properties` (deprecated since 4.0.0): use `Nette\PhpGenerator\ClassType::getProperties()` and `setProperties()` (7326d977)
- **Removed** `Nette\PhpGenerator\Method::$body` (deprecated since 4.0.0): use `Nette\PhpGenerator\Method::getBody()` and `setBody()` (7326d977)
- **Removed** `Nette\PhpGenerator\GlobalFunction::$body` (deprecated since 4.0.0): use `Nette\PhpGenerator\GlobalFunction::getBody()` and `setBody()` (7326d977)
- **Removed** `Nette\PhpGenerator\Closure::$body` (deprecated since 4.0.0): use `Nette\PhpGenerator\Closure::getBody()` and `setBody()` (7326d977)
- **Removed** `Nette\PhpGenerator\Parameter::$defaultValue` (deprecated since 4.0.0): use `Nette\PhpGenerator\Parameter::getDefaultValue()` and `setDefaultValue()` (7326d977)
- **Removed** `Nette\PhpGenerator\Property::$value` (deprecated since 4.0.0): use `Nette\PhpGenerator\Property::getValue()` and `setValue()` (7326d977)
- **Removed** `Nette\PhpGenerator\ClassType::setClass()` (deprecated since 3.6.0): a `Nette\PhpGenerator\ClassType` is always a class (8dd037c6)
- **Removed** `Nette\PhpGenerator\ClassType::setInterface()` (deprecated since 4.0.0): create `new Nette\PhpGenerator\InterfaceType($name)` (8dd037c6)
- **Removed** `Nette\PhpGenerator\ClassType::setTrait()` (deprecated since 4.0.0): create `new Nette\PhpGenerator\TraitType($name)` (8dd037c6)
- **Removed** `Nette\PhpGenerator\ClassType::setType()` (deprecated since 4.0.1): create `new Nette\PhpGenerator\InterfaceType($name)` or `new Nette\PhpGenerator\TraitType($name)` (8dd037c6)
- **Removed** `Nette\PhpGenerator\ClassLike::withBodiesFrom()` (deprecated since 4.0.0): use `Nette\PhpGenerator\ClassLike::from()` with `withBodies: true` (8dd037c6)
- **Removed** `Nette\PhpGenerator\GlobalFunction::withBodyFrom()` (deprecated since 4.0.0): use `Nette\PhpGenerator\GlobalFunction::from()` with `withBody: true` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Helpers::dump()` (deprecated since 3.3.0, marked @internal): use `(new Nette\PhpGenerator\Dumper)->dump()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Helpers::format()` (deprecated since 3.3.0, marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Helpers::formatArgs()` (deprecated since 3.3.0, marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()`, which takes the arguments as a variadic list (8dd037c6)
- **Removed** `Nette\PhpGenerator\Parameter::getTypeHint()` (deprecated since 3.3.0): use `Nette\PhpGenerator\Parameter::getType()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Parameter::setTypeHint()` (deprecated since 3.3.0): use `Nette\PhpGenerator\Parameter::setType()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\PhpFile::getStrictTypes()` (deprecated since 3.3.0): use `Nette\PhpGenerator\PhpFile::hasStrictTypes()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\PhpNamespace::getBracketedSyntax()` (deprecated since 3.3.0): use `Nette\PhpGenerator\PhpNamespace::hasBracketedSyntax()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\PhpNamespace::unresolveName()` (deprecated since 3.6.2): use `Nette\PhpGenerator\PhpNamespace::simplifyName()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Traits\FunctionLike::getReturnNullable()` (deprecated since 3.3.0): use `Nette\PhpGenerator\Traits\FunctionLike::isReturnNullable()` (8dd037c6)
- **Removed** `Nette\PhpGenerator\Type::getType()` (deprecated since 4.0.0): use `get_debug_type()` (8dd037c6)
- **Signature** `Nette\PhpGenerator\ClassLike::from()`: the `$materializeTraits` parameter was removed; it had no effect any more (8dd037c6)
- **Signature** `Nette\PhpGenerator\Factory::fromClassReflection()`: the `$materializeTraits` parameter was removed; it had no effect any more (8dd037c6)
- **Signature** `Nette\PhpGenerator\Traits\TraitsAware::addTrait()`: the second parameter is no longer declared; pass resolutions through `Nette\PhpGenerator\TraitUse::addResolution()` on the returned object (8dd037c6)
- **Signature** `Nette\PhpGenerator\Traits\TraitsAware::setTraits()`: the array must contain `Nette\PhpGenerator\TraitUse` instances; plain trait names are no longer accepted (8dd037c6)
- **Signature** `Nette\PhpGenerator\Traits\ConstantsAware::setConstants()`: the array must contain `Nette\PhpGenerator\Constant` instances; raw values are no longer wrapped automatically (8dd037c6)
- **Signature** `Nette\PhpGenerator\TraitUse::__construct()`: the `$parent` parameter was removed (8dd037c6)

## 4.0.10

- **Renamed** `Nette\PhpGenerator\ClassLike::VisibilityPublic` → `Nette\PhpGenerator\Visibility::Public`; the old name is silently deprecated, also in 4.1.7 (132099e5)
- **Renamed** `Nette\PhpGenerator\ClassLike::VisibilityProtected` → `Nette\PhpGenerator\Visibility::Protected`; the old name is silently deprecated, also in 4.1.7 (132099e5)
- **Renamed** `Nette\PhpGenerator\ClassLike::VisibilityPrivate` → `Nette\PhpGenerator\Visibility::Private`; the old name is silently deprecated, also in 4.1.7 (132099e5)
- **Signature** `$val` of `Nette\PhpGenerator\Traits\VisibilityAware::setVisibility()` is renamed to `$value`; affects callers using the named argument, also in 4.1.7 (132099e5)
- **Signature** `$state` of `Nette\PhpGenerator\Type::nullable()` is renamed to `$nullable`; affects callers using the named argument, also in 4.1.0 (c0b51b61)
- **Changed** `Nette\PhpGenerator\Traits\VisibilityAware::setVisibility()` now throws `\ValueError` for an invalid visibility; previously `Nette\InvalidArgumentException`; affects code catching it, also in 4.1.7 (132099e5)
- **Changed** `Nette\PhpGenerator\Factory::fromClassReflection()` with `withBodies: true` now also throws `Nette\NotSupportedException` for an internal class; previously only for an anonymous one, also in 4.1.1 (250bf02d)
- **Changed** `Nette\PhpGenerator\Factory::fromFunctionReflection()` with `withBody: true` now also throws `Nette\NotSupportedException` for an internal function; previously only for a closure, also in 4.1.1 (250bf02d)

## 4.0.9

- **Signature** `$class` of `Nette\PhpGenerator\PhpNamespace::add()` is typed `ClassType|InterfaceType|TraitType|EnumType` instead of `Nette\PhpGenerator\ClassLike`; affects code passing another descendant of `Nette\PhpGenerator\ClassLike` (163c5e9e)

## 4.0.8

- **Changed** `Nette\PhpGenerator\Factory::fromPropertyReflection()` no longer marks a property of a PHP 8.2 readonly class as readonly; previously every reflected readonly property was marked (ea40f2f8)

## 4.0.6

- **Changed** `Nette\PhpGenerator\Printer::printFunction()` now prints the opening brace on its own line whenever the function has a return type, even when the parameter list did not wrap (43d61017)

## 4.0.3

- **Changed** `Nette\PhpGenerator\PsrPrinter` now separates groups of `use` declarations (classes, functions, constants) by a blank line; set `Nette\PhpGenerator\PsrPrinter::$linesBetweenUseTypes` to `0` to keep the previous output (90c76b5e)

## 4.0.2

- **Removed** `Nette\PhpGenerator\TraitType::addConstant()`, together with `getConstants()`, `setConstants()` and `removeConstant()`: a trait cannot declare constants; they are available again from 4.0.5 (5de75b65)
- **Signature** `$member` of `Nette\PhpGenerator\TraitType::addMember()` no longer accepts `Nette\PhpGenerator\Constant` (5de75b65)
- **Signature** `$class` of `Nette\PhpGenerator\Printer::printClass()` is typed `ClassType|InterfaceType|TraitType|EnumType` instead of `Nette\PhpGenerator\ClassLike`; affects subclasses overriding it (7fe136bf)
- **Changed** `Nette\PhpGenerator\Printer::printParameters()` now always ends a wrapped parameter list with a trailing comma; previously only when a variadic or promoted parameter or an attribute was present (5c75670b)
- **Changed** `Nette\PhpGenerator\Printer::printClosure()` now always ends a wrapped `use` list with a trailing comma; previously never (5c75670b)

## 4.0.1

- **Removed** `Nette\PhpGenerator\ClassType::addExtend()`: a class extends at most one class, use `Nette\PhpGenerator\ClassType::setExtends()`; for an interface use `Nette\PhpGenerator\InterfaceType::addExtend()` (d003c62c)
- **Replaced** `Nette\PhpGenerator\ClassType` with `Nette\PhpGenerator\ClassLike` as the common representation of a class, interface, trait and enum: `Nette\PhpGenerator\ClassType` now represents only a class, the others are `Nette\PhpGenerator\InterfaceType`, `Nette\PhpGenerator\TraitType` and `Nette\PhpGenerator\EnumType`; code type-hinting `Nette\PhpGenerator\ClassType` to accept any of them must type-hint `Nette\PhpGenerator\ClassLike` (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::class()`: create `new Nette\PhpGenerator\ClassType($name)`; the old name is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::interface()`: create `new Nette\PhpGenerator\InterfaceType($name)`; the old name is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::trait()`: create `new Nette\PhpGenerator\TraitType($name)`; the old name is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::enum()`: create `new Nette\PhpGenerator\EnumType($name)`; the old name is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::setType()`: create `new Nette\PhpGenerator\InterfaceType($name)` or `new Nette\PhpGenerator\TraitType($name)`; using it triggers `E_USER_DEPRECATED` (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::getType()`: it always returns `'class'`, test the object with `instanceof` instead; the old name is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_CLASS`: there is no replacement; the constant is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_INTERFACE`: there is no replacement; the constant is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_TRAIT`: there is no replacement; the constant is silently deprecated (d003c62c)
- **Deprecated** `Nette\PhpGenerator\ClassType::TYPE_ENUM`: there is no replacement; the constant is silently deprecated (d003c62c)
- **Signature** `Nette\PhpGenerator\ClassType::setExtends()`: takes `?string` instead of `string|array`; for multiple parents use `Nette\PhpGenerator\InterfaceType::setExtends()`, which still accepts an array (d003c62c)
- **Signature** `Nette\PhpGenerator\ClassType::getExtends()`: returns `?string` instead of `string|array` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpFile::addInterface()`: returns `Nette\PhpGenerator\InterfaceType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpFile::addTrait()`: returns `Nette\PhpGenerator\TraitType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpFile::addEnum()`: returns `Nette\PhpGenerator\EnumType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpNamespace::addInterface()`: returns `Nette\PhpGenerator\InterfaceType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpNamespace::addTrait()`: returns `Nette\PhpGenerator\TraitType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpNamespace::addEnum()`: returns `Nette\PhpGenerator\EnumType` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpFile::getClasses()`: returns `Nette\PhpGenerator\ClassLike` objects, which may be interfaces, traits or enums; previously always `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\PhpNamespace::getClasses()`: returns `Nette\PhpGenerator\ClassLike` objects, which may be interfaces, traits or enums; previously always `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\ClassLike::from()`: returns `Nette\PhpGenerator\ClassLike`; previously `Nette\PhpGenerator\ClassType::from()` always returned a class (d003c62c)
- **Signature** `Nette\PhpGenerator\ClassLike::fromCode()`: returns `Nette\PhpGenerator\ClassLike`; previously `Nette\PhpGenerator\ClassType::fromCode()` always returned a class (d003c62c)
- **Signature** `Nette\PhpGenerator\Factory::fromClassReflection()`: returns `Nette\PhpGenerator\ClassLike` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `Nette\PhpGenerator\Factory::fromClassCode()`: returns `Nette\PhpGenerator\ClassLike` instead of `Nette\PhpGenerator\ClassType` (d003c62c)
- **Signature** `$class` of `Nette\PhpGenerator\Printer::printClass()` is typed `Nette\PhpGenerator\ClassLike` instead of `Nette\PhpGenerator\ClassType`; affects subclasses overriding it (d003c62c)
- **Changed** `Nette\PhpGenerator\ClassType::setType()` no longer accepts `'enum'`; it throws `Nette\InvalidArgumentException`, create `new Nette\PhpGenerator\EnumType($name)` instead (d003c62c)
- **Changed** `Nette\PhpGenerator\InterfaceType` has no `addTrait()`, `getTraits()`, `setTraits()` or `removeTrait()`; previously an interface was a `Nette\PhpGenerator\ClassType` and accepted traits (d003c62c)
- **Changed** `Nette\PhpGenerator\ClassLike::fromCode()` now keeps an explicit `public` modifier written in the source; previously it was normalized away like an omitted one, so the re-printed output may now contain `public` where it did not (e4e32ef4)

## 4.0.0

- **Removed** `Nette\PhpGenerator\Traits\FunctionLike::setNamespace()` (deprecated since 3.2): there is no replacement (fc77854c)
- **Removed** `Nette\PhpGenerator\Parameter::setOptional()` (deprecated since 3.2): use `Nette\PhpGenerator\Parameter::setDefaultValue()`, which requires an actual default value (fc77854c)
- **Removed** `Nette\PhpGenerator\ClassType::getTraitResolutions()` (marked @internal): use `Nette\PhpGenerator\ClassType::getTraits()`, which returns `Nette\PhpGenerator\TraitUse` objects (f7899d91)
- **Deprecated** `Nette\PhpGenerator\ClassType::$methods`: use `Nette\PhpGenerator\ClassType::getMethods()` and `setMethods()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\ClassType::$properties`: use `Nette\PhpGenerator\ClassType::getProperties()` and `setProperties()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\Method::$body`: use `Nette\PhpGenerator\Method::getBody()` and `setBody()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\GlobalFunction::$body`: use `Nette\PhpGenerator\GlobalFunction::getBody()` and `setBody()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\Closure::$body`: use `Nette\PhpGenerator\Closure::getBody()` and `setBody()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\Parameter::$defaultValue`: use `Nette\PhpGenerator\Parameter::getDefaultValue()` and `setDefaultValue()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\Property::$value`: use `Nette\PhpGenerator\Property::getValue()` and `setValue()`; using the old name triggers `E_USER_DEPRECATED` (739de576)
- **Deprecated** `Nette\PhpGenerator\PhpLiteral`: use `Nette\PhpGenerator\Literal`; the old name is silently deprecated (alias since 3.3.3) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\ClassType::setClass()`: use `Nette\PhpGenerator\ClassType::setType()` with `'class'`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\ClassType::setInterface()`: use `Nette\PhpGenerator\ClassType::interface()`; using the old name triggers `E_USER_DEPRECATED` (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\ClassType::setTrait()`: use `Nette\PhpGenerator\ClassType::trait()`; using the old name triggers `E_USER_DEPRECATED` (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\ClassType::withBodiesFrom()`: use `Nette\PhpGenerator\ClassType::from()` with `withBodies: true`; using the old name triggers `E_USER_DEPRECATED` (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\GlobalFunction::withBodyFrom()`: use `Nette\PhpGenerator\GlobalFunction::from()` with `withBody: true`; using the old name triggers `E_USER_DEPRECATED` (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Helpers::dump()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->dump()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Helpers::format()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Helpers::formatArgs()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()`, which takes the arguments as a variadic list; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Parameter::getTypeHint()`: use `Nette\PhpGenerator\Parameter::getType()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Parameter::setTypeHint()`: use `Nette\PhpGenerator\Parameter::setType()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\PhpFile::getStrictTypes()`: use `Nette\PhpGenerator\PhpFile::hasStrictTypes()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::getBracketedSyntax()`: use `Nette\PhpGenerator\PhpNamespace::hasBracketedSyntax()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::unresolveName()`: use `Nette\PhpGenerator\PhpNamespace::simplifyName()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.6.2) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Traits\FunctionLike::getReturnNullable()`: use `Nette\PhpGenerator\Traits\FunctionLike::isReturnNullable()`; using the old name triggers `E_USER_DEPRECATED` (deprecated since 3.3.0) (c1d720e1)
- **Deprecated** `Nette\PhpGenerator\Type::getType()`: use `get_debug_type()`; using the old name triggers `E_USER_DEPRECATED` (c1d720e1)
- **Deprecated** `$materializeTraits` of `Nette\PhpGenerator\ClassType::from()`: it has no effect any more, passing anything but `null` triggers `E_USER_DEPRECATED` (af009917)
- **Deprecated** `$materializeTraits` of `Nette\PhpGenerator\Factory::fromClassReflection()`: it has no effect any more, passing anything but `null` triggers `E_USER_DEPRECATED` (af009917)
- **Deprecated** scalar values in `$consts` of `Nette\PhpGenerator\ClassType::setConstants()`: pass `Nette\PhpGenerator\Constant` instances, a scalar still works but triggers `E_USER_DEPRECATED` (0d035441)
- **Deprecated** plain names in `$traits` of `Nette\PhpGenerator\ClassType::setTraits()`: pass `Nette\PhpGenerator\TraitUse` instances, a string still works but triggers `E_USER_DEPRECATED` (0d035441)
- **Signature** native PHP 8 parameter and return types were added across the package and fluent setters declare `static` instead of `self`; affects subclasses overriding them and callers passing an incompatible value, notably `Nette\PhpGenerator\ClassType::from()`, `Nette\PhpGenerator\ClassType::addMember()`, `Nette\PhpGenerator\ClassType::setExtends()`, `Nette\PhpGenerator\ClassType::addCase()`, `Nette\PhpGenerator\ClassType::withBodiesFrom()`, `Nette\PhpGenerator\Method::from()`, `Nette\PhpGenerator\EnumCase::setValue()`, `Nette\PhpGenerator\PhpFile::addNamespace()`, `Nette\PhpGenerator\Printer::printParameters()`, `Nette\PhpGenerator\Property::setValue()` and `Nette\PhpGenerator\Parameter::setDefaultValue()` (9b3e3afc)
- **Signature** `Nette\PhpGenerator\Dumper::$maxDepth`, `Nette\PhpGenerator\Dumper::$wrapLength` and `Nette\PhpGenerator\Dumper::$indentation` are typed `int`, `int` and `string`; assigning an incompatible value now throws a `TypeError` instead of being stored (c181ffc1)
- **Signature** `Nette\PhpGenerator\ClassType::getTraits()`: returns `Nette\PhpGenerator\TraitUse` objects instead of trait names; read the name with `Nette\PhpGenerator\TraitUse::getName()` (f7899d91)
- **Signature** `Nette\PhpGenerator\ClassType::addTrait()`: always returns `Nette\PhpGenerator\TraitUse` and its second parameter is renamed to `$deprecatedParam`; chaining `Nette\PhpGenerator\ClassType` methods on the result no longer works and resolutions are set with `Nette\PhpGenerator\TraitUse::addResolution()` (1270e123)
- **Signature** `Nette\PhpGenerator\Method::setBody()`: `$code` is a non-nullable `string` and `getBody()` returns `string`; the method is inherited from `Nette\PhpGenerator\Traits\FunctionLike` and no longer accepts `null` (059a93b9)
- **Signature** `Nette\PhpGenerator\Printer::printMethod()`: new third parameter `bool $isInterface = false`; pass `true` when printing an interface method outside `printClass()` (059a93b9)
- **Changed** `Nette\PhpGenerator\ClassType::addMethod()` now throws `Nette\InvalidStateException` when the name is already used; previously it silently replaced the existing member (93166ff2)
- **Changed** `Nette\PhpGenerator\ClassType::addMember()` now throws `Nette\InvalidStateException` when the name is already used; previously it silently replaced the existing member (93166ff2)
- **Changed** `Nette\PhpGenerator\ClassType::addCase()` now throws `Nette\InvalidStateException` when the name is already used; previously it silently replaced the existing case (93166ff2)
- **Changed** `Nette\PhpGenerator\ClassType::addTrait()` now throws `Nette\InvalidStateException` when the trait is already used; previously it silently replaced the existing entry (93166ff2)
- **Changed** `Nette\PhpGenerator\PhpNamespace::add()` now throws `Nette\InvalidStateException` when the name already exists in the namespace; previously it silently replaced the previous declaration (c48096c4)
- **Changed** `Nette\PhpGenerator\PhpNamespace::addFunction()` now throws `Nette\InvalidStateException` when the name already exists in the namespace; previously it silently replaced the previous declaration (c48096c4)
- **Changed** `Nette\PhpGenerator\ClassType::addMember()` now throws a native `TypeError` for an unsupported member; previously `Nette\InvalidArgumentException`; affects code catching it (9b3e3afc)
- **Changed** `Nette\PhpGenerator\ClassType::setExtends()` now throws a native `TypeError` for a wrong argument type; previously `Nette\InvalidArgumentException`; affects code catching it (9b3e3afc)
- **Changed** `Nette\PhpGenerator\PhpFile::addNamespace()` now throws a native `TypeError` for a wrong argument type; previously `Nette\InvalidArgumentException`; affects code catching it (9b3e3afc)
- **Changed** `Nette\PhpGenerator\ClassType::from()` without `$materializeTraits` now generates a `use` statement with its resolutions instead of copying the trait members into the class; previously the members were materialized (af009917)
- **Changed** `Nette\PhpGenerator\Method::getBody()` returns `''` for an interface method; previously `null`, and the `abstract` keyword is now decided by the printer from the class kind (059a93b9)
- **Changed** `Nette\PhpGenerator\Type::getType()` returns `'null'` for `null`; previously `null` (9a2d6a8f)
- **Changed** `Nette\PhpGenerator\Dumper` now prints a comma after the last argument of a wrapped argument list; previously only between arguments (a50da77e)

## 3.6.9

- **Renamed** `Nette\PhpGenerator\Type::STRING` → `Nette\PhpGenerator\Type::String`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::INT` → `Nette\PhpGenerator\Type::Int`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::FLOAT` → `Nette\PhpGenerator\Type::Float`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::BOOL` → `Nette\PhpGenerator\Type::Bool`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::ARRAY` → `Nette\PhpGenerator\Type::Array`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::OBJECT` → `Nette\PhpGenerator\Type::Object`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::CALLABLE` → `Nette\PhpGenerator\Type::Callable`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::ITERABLE` → `Nette\PhpGenerator\Type::Iterable`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::VOID` → `Nette\PhpGenerator\Type::Void`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::NEVER` → `Nette\PhpGenerator\Type::Never`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::MIXED` → `Nette\PhpGenerator\Type::Mixed`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::FALSE` → `Nette\PhpGenerator\Type::False`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::NULL` → `Nette\PhpGenerator\Type::Null`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::SELF` → `Nette\PhpGenerator\Type::Self`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::PARENT` → `Nette\PhpGenerator\Type::Parent`; the old name is silently deprecated, also in 4.0.5 (d31782f7)
- **Renamed** `Nette\PhpGenerator\Type::STATIC` → `Nette\PhpGenerator\Type::Static`; the old name is silently deprecated, also in 4.0.5 (d31782f7)

## 3.6.7

- **Renamed** `Nette\PhpGenerator\ClassType::VISIBILITY_PUBLIC` → `Nette\PhpGenerator\ClassType::VisibilityPublic`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Renamed** `Nette\PhpGenerator\ClassType::VISIBILITY_PROTECTED` → `Nette\PhpGenerator\ClassType::VisibilityProtected`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Renamed** `Nette\PhpGenerator\ClassType::VISIBILITY_PRIVATE` → `Nette\PhpGenerator\ClassType::VisibilityPrivate`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Renamed** `Nette\PhpGenerator\PhpNamespace::NAME_NORMAL` → `Nette\PhpGenerator\PhpNamespace::NameNormal`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Renamed** `Nette\PhpGenerator\PhpNamespace::NAME_FUNCTION` → `Nette\PhpGenerator\PhpNamespace::NameFunction`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Renamed** `Nette\PhpGenerator\PhpNamespace::NAME_CONSTANT` → `Nette\PhpGenerator\PhpNamespace::NameConstant`; the old name still works as an alias, also in 4.0.1 (fdc5e146)
- **Changed** `Nette\PhpGenerator\ClassType` now clones its trait list too; previously the clone shared the same `Nette\PhpGenerator\TraitUse` instances with the original, also in 4.0.1 (30e76bc6)

## 3.6.5

- **Changed** `Nette\PhpGenerator\PhpNamespace::getUses()` now omits entries pointing to a name in the namespace itself, so the result may contain fewer items than before, also in 4.0.5 (e973ad0d)

## 3.6.4

- **Signature** `Nette\PhpGenerator\PhpNamespace::addUse()`: the by-reference parameter `$aliasOut` was removed and a new `$of` parameter took its place (8a12f1df)
- **Changed** `Nette\PhpGenerator\ClassType` now keys methods by lowercased name, so `getMethod()`, `hasMethod()` and `removeMethod()` match case-insensitively and adding a method differing only in case replaces the existing one; previously the match was exact (3db5decb)
- **Changed** `Nette\PhpGenerator\PhpNamespace` now keys classes and functions by lowercased name, so adding one differing only in case replaces the existing one and a colliding alias throws `Nette\InvalidStateException`; previously both could coexist (5a39bcfc, 90082c1a)

## 3.6.2

- **Renamed** `Nette\PhpGenerator\PhpNamespace::unresolveType()` → `Nette\PhpGenerator\PhpNamespace::simplifyType()`; the old name was removed (224209aa)
- **Renamed** `Nette\PhpGenerator\PhpNamespace::unresolveName()` → `Nette\PhpGenerator\PhpNamespace::simplifyName()`; the old name is silently deprecated (224209aa)
- **Replaced** the `wrapLength` of the `Nette\PhpGenerator\Printer` dumper with `Nette\PhpGenerator\Printer::$wrapLength`: the printer overwrites the dumper's value on every call, so a subclass setting it on the dumper has it silently discarded (bce6abcd)
- **Signature** `Nette\PhpGenerator\Printer::printParameters()`: became `protected` and the `$namespace` parameter was removed, the namespace is taken from `Nette\PhpGenerator\Printer::$namespace`; affects subclasses overriding it and any direct caller (0194e2d9)
- **Signature** `Nette\PhpGenerator\Printer::printType()`: became `protected`, the `$namespace` parameter was removed and `$nullable` lost its default, so it is required; affects subclasses overriding it and any direct caller (0194e2d9)
- **Signature** `Nette\PhpGenerator\ClassType::addTrait()`: `$resolutions` is no longer typed `array` and the declared `self` return type was dropped; passing `true` returns a `Nette\PhpGenerator\TraitUse` (1cc5a685)
- **Signature** `Nette\PhpGenerator\Traits\FunctionLike::getReturnType()`: new parameter `bool $asObject = false` and the declared `?string` return type was dropped, it returns `Nette\Utils\Type|string|null` (4f4af9ab)
- **Signature** `Nette\PhpGenerator\Parameter::getType()`: new parameter `bool $asObject = false` and the declared `?string` return type was dropped, it returns `Nette\Utils\Type|string|null` (4f4af9ab)
- **Signature** `Nette\PhpGenerator\Property::getType()`: new parameter `bool $asObject = false` and the declared `?string` return type was dropped, it returns `Nette\Utils\Type|string|null` (4f4af9ab)
- **Changed** `Nette\PhpGenerator\ClassType::getTraitResolutions()` (marked @internal) now returns `Nette\PhpGenerator\TraitUse` objects; previously arrays of resolution strings (1cc5a685)
- **Changed** `Nette\PhpGenerator\ClassType::setMethods()` now throws a native `TypeError` for an element that is not a `Nette\PhpGenerator\Method`; previously `Nette\InvalidArgumentException`; affects code catching it (a2e6a0c3)
- **Changed** `Nette\PhpGenerator\ClassType::setProperties()` now throws a native `TypeError` for an element that is not a `Nette\PhpGenerator\Property`; previously `Nette\InvalidArgumentException`; affects code catching it (a2e6a0c3)
- **Changed** `Nette\PhpGenerator\Traits\FunctionLike::setParameters()` now throws a native `TypeError` for an element that is not a `Nette\PhpGenerator\Parameter`; previously `Nette\InvalidArgumentException`; affects code catching it (a2e6a0c3)
- **Changed** `Nette\PhpGenerator\ClassType::setConstants()` now prints constants added as plain values with an explicit `public` modifier; previously without a visibility modifier (a2e6a0c3)
- **Changed** `Nette\PhpGenerator\ClassType::setName()` now rejects PHP reserved keywords as class names; previously only an invalid identifier was rejected (42884e1e)
- **Changed** `Nette\PhpGenerator\Traits\FunctionLike::setReturnType()` now validates the type and throws `Nette\InvalidArgumentException` for invalid syntax; previously any string was accepted (e0741f46)
- **Changed** `Nette\PhpGenerator\PhpNamespace::addUse()` now throws `Nette\InvalidArgumentException` when the name or the alias is not a valid identifier or is a reserved keyword; previously such values were accepted (b05a3ae8)
- **Changed** `Nette\PhpGenerator\Property::setReadOnly()` makes printing throw `Nette\InvalidStateException` when the property has no type; previously invalid PHP was printed (0ba04ffc)
- **Changed** `Nette\PhpGenerator\PromotedParameter::setReadOnly()` makes printing throw `Nette\InvalidStateException` when the parameter has no type; previously invalid PHP was printed (0ba04ffc)
- **Changed** `Nette\PhpGenerator\Dumper::format()` no longer treats `?` as a placeholder when it is immediately followed by a word character; such text is left as is (018ed92f)
- **Changed** `Nette\PhpGenerator\Literal` now formats its arguments lazily on stringification; previously in the constructor, so invalid arguments failed there and later mutations of referenced objects were not reflected (8a3d6cb8)
- **Changed** `Nette\PhpGenerator\Printer::__construct()` now initializes the dumper; a subclass overriding the constructor must call `parent::__construct()` or printing fails (31779e23)
- **Changed** `Nette\PhpGenerator\Traits\CommentAware::setComment()` text containing `*/` is now escaped as `* /` when printed, so the docblock is not closed prematurely (a758e6f1)

## 3.6.1

- **Signature** `Nette\PhpGenerator\ClassType::class()`: the `$namespace` parameter was removed and `$name` lost its default, so it is required although still nullable (eb7c840e)
- **Signature** `Nette\PhpGenerator\ClassType::interface()`: the `$namespace` parameter was removed and `$name` is now required and non-nullable (eb7c840e)
- **Signature** `Nette\PhpGenerator\ClassType::trait()`: the `$namespace` parameter was removed and `$name` is now required and non-nullable (eb7c840e)
- **Signature** `Nette\PhpGenerator\ClassType::enum()`: the `$namespace` parameter was removed and `$name` is now required and non-nullable (eb7c840e)
- **Changed** `Nette\PhpGenerator\ClassType::addConstant()` now marks the constant `public`, so the printed output contains the modifier; previously no visibility was set (1495adb5)

## 3.6.0

- **Renamed** `Nette\PhpGenerator\PhpNamespace::unresolveUnionType()` → `Nette\PhpGenerator\PhpNamespace::unresolveType()`; the old name was removed (c6f02b69)
- **Deprecated** `Nette\PhpGenerator\ClassType::setClass()`: use `Nette\PhpGenerator\ClassType::setType()` with `'class'`; the old name is silently deprecated (3122e00d)
- **Changed** `Nette\PhpGenerator\Dumper::dump()` now dumps a static closure as `\Closure::fromCallable(...)` code; previously it threw `Nette\InvalidArgumentException` (4e7be9e2)

## 3.5.4

- **Changed** `Nette\PhpGenerator\Traits\FunctionLike::setReturnType()` now strips a leading `?` and sets the return-nullable flag; previously `'?type'` was stored literally (4cf1dd12)
- **Changed** `Nette\PhpGenerator\Parameter::setType()` now strips a leading `?` and sets the nullable flag; previously `'?type'` was stored literally (4cf1dd12)
- **Changed** `Nette\PhpGenerator\Property::setType()` now strips a leading `?` and sets the nullable flag; previously `'?type'` was stored literally (4cf1dd12)

## 3.5.3

- **Signature** `Nette\PhpGenerator\Printer::printParameters()`: new third parameter `int $column = 0`; affects subclasses overriding it (f6624444)
- **Changed** `Nette\PhpGenerator\Printer::printMethod()` now counts the function name and the return type into the line length, so long signatures wrap onto several lines sooner than before (f6624444)

## 3.5.2

- **Changed** `Nette\PhpGenerator\Property::setValue()` now turns on the initialized state, and `isInitialized()` returns `true` for any non-null value; previously it had to be set with `setInitialized()` (fa9d71a8)
- **Changed** `Nette\PhpGenerator\ClassType::addProperty()` called without a value no longer sets an implicit default of `null`; previously `setValue(null)` was always applied (fa9d71a8)

## 3.5.1

- **Changed** `Nette\PhpGenerator\Dumper::format()` prints the `...?` placeholder as a plain list of values again, and named arguments are produced by the new `...?:` placeholder; in 3.5.0 `...?` printed string keys as named arguments (5cacadcd)

## 3.5.0

- **Signature** `$name` of `Nette\PhpGenerator\PhpFile::addNamespace()` is renamed to `$namespace` and also accepts a `Nette\PhpGenerator\PhpNamespace`; affects callers using the named argument (6c1bd70f)
- **Changed** `Nette\PhpGenerator\Dumper::format()` prints an array with string keys passed to the `...?` placeholder as named arguments; previously as a plain list of values (0536bd07)

## 3.4.1

- **Removed** `Nette\PhpGenerator\Helpers::indentPhp()` (marked @internal): use `Nette\PhpGenerator\Helpers::unindent()`, which only dedents and drops both the indentation-character parameter and the token-aware protection of string and heredoc contents (7051954c)
- **Changed** `Nette\PhpGenerator\PhpNamespace::unresolveName()` now returns the type names `'static'`, `'mixed'`, `'false'` and `'null'` as they are; previously they were treated as class names eligible for an import or FQN resolution (39b8959e)
- **Changed** `Nette\PhpGenerator\ClassType::withBodiesFrom()` now replaces multi-line and heredoc string literals in the extracted bodies with single-line double-quoted strings; previously their original form was preserved (4662099f)

## 3.4.0

- **Changed** `Nette\PhpGenerator\Factory::fromClassReflection()` now reads class constants through reflection constants, so only constants declared in the class itself are included and their visibility and doc comment are preserved; previously inherited constants were included too and every constant came out public and undocumented (8079531a)
- **Changed** `Nette\PhpGenerator\Printer::printClass()` now separates a constant that has a comment from the following constant by one blank line (262cd4c2)
- **Changed** `Nette\PhpGenerator\Printer::printClass()` applies that blank-line separation only before a declaration ending with `;`, so one-line properties without a comment are not separated (d422cac0)

## 3.3.4

- **Replaced** `Nette\PhpGenerator\Printer::printReturnType()` with `Nette\PhpGenerator\Printer::printType()`: the old method became `private` and can no longer be overridden, the new one formats property and parameter types as well (0ef04440)
- **Signature** `Nette\PhpGenerator\Printer::printParameters()`: became `public` and `$namespace` got a default of `null`; a subclass overriding it as `protected` now fails with a visibility mismatch (ea96bdc4)

## 3.3.3

- **Renamed** `Nette\PhpGenerator\PhpLiteral` → `Nette\PhpGenerator\Literal`; the old name still works as an alias (02a0107a)
- **Changed** `Nette\PhpGenerator\Factory::fromParameterReflection()` now wraps a constant default value in `Nette\PhpGenerator\Literal`; code testing the result with `instanceof Nette\PhpGenerator\PhpLiteral` no longer matches (02a0107a)

## 3.3.0

- **Removed** `Nette\PhpGenerator\Helpers::INDENT_LENGTH`: there is no replacement (6e1cc8df)
- **Replaced** `Nette\PhpGenerator\Helpers::WRAP_LENGTH` with `Nette\PhpGenerator\Dumper::$wrapLength`: it is an instance property now, set it on a `Nette\PhpGenerator\Dumper` object instead of reading a class constant (6e1cc8df, 15be6e8f)
- **Deprecated** `Nette\PhpGenerator\Helpers::dump()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->dump()`, the method is no longer static; the old name is silently deprecated (6e1cc8df, e7b44410)
- **Deprecated** `Nette\PhpGenerator\Helpers::format()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()`, the method is no longer static; the old name is silently deprecated (6e1cc8df, e7b44410)
- **Deprecated** `Nette\PhpGenerator\Helpers::formatArgs()` (marked @internal): use `(new Nette\PhpGenerator\Dumper)->format()`, which takes the arguments as a variadic list instead of an array; the old name is silently deprecated (6e1cc8df)
- **Deprecated** `Nette\PhpGenerator\Parameter::getTypeHint()`: use `Nette\PhpGenerator\Parameter::getType()`; the old name is silently deprecated (173dd9d8)
- **Deprecated** `Nette\PhpGenerator\Parameter::setTypeHint()`: use `Nette\PhpGenerator\Parameter::setType()`; the old name is silently deprecated (173dd9d8)
- **Deprecated** `Nette\PhpGenerator\PhpFile::getStrictTypes()`: use `Nette\PhpGenerator\PhpFile::hasStrictTypes()`; the old name is silently deprecated (173dd9d8)
- **Deprecated** `Nette\PhpGenerator\PhpNamespace::getBracketedSyntax()`: use `Nette\PhpGenerator\PhpNamespace::hasBracketedSyntax()`; the old name is silently deprecated (173dd9d8)
- **Deprecated** `Nette\PhpGenerator\Traits\FunctionLike::getReturnNullable()`: use `Nette\PhpGenerator\Traits\FunctionLike::isReturnNullable()`; the old name is silently deprecated (173dd9d8)
- **Signature** `$hint` of `Nette\PhpGenerator\Parameter::setTypeHint()` is renamed to `$type`; affects callers using the named argument (173dd9d8)
- **Signature** `Nette\PhpGenerator\Printer::dump()`: new parameter `int $column = 0`; affects subclasses overriding it (1d0d54c5)
- **Changed** `Nette\PhpGenerator\Dumper::$wrapLength` defaults to `120`; previously `Nette\PhpGenerator\Helpers::WRAP_LENGTH` was `100`, so arrays and argument lists wrap onto several lines later than before (758077b9)
- **Changed** `Nette\PhpGenerator\ClassType::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\Method::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\GlobalFunction::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\Closure::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\PhpFile::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\PhpNamespace::__toString()` lets an exception raised while printing propagate on PHP 7.4+; previously it was converted to `E_USER_ERROR` (7676b10b)
- **Changed** `Nette\PhpGenerator\Dumper::dump()` now trims a `Nette\PhpGenerator\Literal` value and re-indents it to the current level; previously it was inserted verbatim (fee9c6c1)
