# PhpGenerator internals

How `nette/php-generator` handles the parts that are **not** obvious from the
object model, for agents editing it. Most of the package — `ClassType`,
`Method`, `Property`, the `Traits/*` — is a self-explanatory builder and does not
belong here. The expensive-to-reconstruct knowledge is concentrated in three
places — **type handling, the name/namespace model, and the reflection↔source
round-trip** — plus two cross-cutting contracts: tagged names and print-time
validation. One file.

## Type handling: the type object is the single truth, nullability lives inside it

A member's type is stored as a `Nette\Utils\Type` object (`Parameter`,
`Property`, `Constant`, function return) and nullability is part of it:
`setType('?int')` and `setType('int|null')` produce the same type. The other
sources of nullability are **folded into the type** by `applyNullable()` at
the end of every setter:

- **`setNullable()`** adds `|null` to the type (`false` removes it) and
  **throws without a type**, `setNullable(false)` as well: there is nothing to
  attach the null to, so `setType()` goes first.
- **A `null` default** (`Parameter::setDefaultValue(null)`) or a `null` initial
  value (`Property::setValue(null)`, `setInitialized()`) adds null to the type as
  well, in either order of the calls. Folding is one-way: a later
  `setDefaultValue(5)` does **not** take the null back, and `setNullable(false)`
  over a null default is immediately undone by the fold. Return types have no
  default and no inference.
- **`isNullable()` reads the type** (`allows('null')`), so it is `true` for
  `mixed` and `null` too. Without a type it falls back to a `null` default
  (`Parameter`) or a `null` initial value (`Property`).

**`getType()` returns the object**; the declaration string is `(string) $type`
and `Helpers::nullableType($type, false)` strips the null part (`?int` → `int`,
`(A&B)|null` → `A&B`; `null` and `mixed` stay).

The Printer prints `(string) $type` simplified against the namespace, so the
printed spelling is the one `Nette\Utils\Type` produces: `?T` for a simple
nullable type, `A|B|null` for a union, `(A&B)|null` for DNF. **Input spelling is
not preserved**: `fromCode()` followed by printing turns `int|null` into `?int`.

**Order trap in loaders:** `Factory::fromPropertyReflection()` calls
`setValue(null)` for a property without a default and then `setInitialized(false)`.
`setType()` must come **after** `setInitialized()`, otherwise the transient
"initialized with null" state folds a null into the type.

## The type API is duplicated across five classes

`setType`/`getType` are **duplicated, not shared via a trait**, and differ:

| Class | `setType` validates? | `getType()` returns | nullable API? |
|---|---|---|---|
| `Parameter`, `Property`, function **return** | yes (`Nette\Utils\Type::fromString`) | `?Nette\Utils\Type` | `setNullable`/`isNullable` |
| `Constant` | yes | `?Nette\Utils\Type` | **none** |
| `EnumType` | **no validation** (it is the enum *backing* type, int/string) | `?string` | n/a |

`EnumType::setType()` stores anything, and the backing type is checked only by
`EnumType::validate()` at print time. An agent "unifying" type handling must
account for these deliberate per-class differences.

## Name model: `getName()` is short, `getFullName()` is the FQN

`ClassLike::getName()` returns the **short** name and `getFullName()` the FQN —
**the opposite of `ReflectionClass::getName()`** (which returns the FQN). The
constructor splits an incoming FQN into namespace + short name.

- **`Factory::createClassObject` is internally inconsistent**: `ClassType` is built
  from the **short** name, but `EnumType`/`InterfaceType`/`TraitType` from the
  **FQN**. The effect is neutralized (the `ClassLike` constructor re-splits an FQN),
  but the asymmetry is there and surprises.
- **`PhpNamespace` is the resolution engine.** `resolveName` expands a short/aliased
  name to an FQN; `simplifyName` reduces an FQN to the shortest form allowed by the
  current `use` statements (or a namespace-relative form); `simplifyType` runs
  `simplifyName` over every name in a type string via regex. There are **separate
  alias tables** per symbol kind (class / function / constant, the `$of`
  argument).
- **`self`/`parent`/`static` are keywords** (`Helpers::Keywords`), so both
  `resolveName` and `simplifyName` leave them untouched — which sets up the
  round-trip trap below.
- **Case sensitivity mirrors PHP, not API consistency.** Methods, classes, and
  functions are keyed **case-insensitively** (lowercased keys in
  `Traits/MethodsAware` and `PhpNamespace`, `strcasecmp` for aliases);
  properties, constants, and enum cases are **case-sensitive**. So
  `hasMethod('FOO')` matches `foo` while `hasProperty('FOO')` does not. Getters
  (`getMethods()`, `getClasses()`, `getFunctions()`) rebuild their arrays keyed
  by the original-case names — the lowercase keys are internal only.

## Tagged names: how resolution reaches into opaque strings

Bodies, attribute arguments, and dumped values are plain strings the printer
cannot parse. A name inside them stays resolvable only when wrapped in an
**invisible comment tag**: `Helpers::tagName` prefixes it with `/*(n*/`,
`/*(f*/`, or `/*(c*/` (class / function / constant), and the Printer runs
`Helpers::simplifyTaggedNames` over exactly four outputs — function bodies,
trait resolutions, attribute arguments, and every `dump()`ed value — replacing
each tag with `simplifyType` against the current namespace.

- **Producers:** `Extractor` tags every name the parser resolved as
  fully-qualified in extracted bodies; `Factory` tags class constants used as
  parameter defaults. Keywords are never tagged.
- **Consequence:** an untagged FQN in a body prints verbatim; only a tagged one
  follows the namespace's `use` statements. Any regex or transformation over
  body strings must expect these markers.

## Round-trip: Factory (reflection) vs Extractor (php-parser), and what stays context-bound

Two independent sources build objects from existing code:

- **`Factory`** uses reflection only — **no I/O, no php-parser** for signatures.
- **`Extractor`** parses source with **nikic/php-parser** and is reached two ways:
  when bodies are requested (`withBodies`/`withBody`), and as the whole loading
  path of `fromCode()`/`fromClassCode()`, where *everything* (members, defaults,
  bodies) comes from the parsed source. Its constructor **throws**
  `NotSupportedException` if php-parser is absent.

**Determinism is protected by fail-fast, not silent degradation.** Body loading is
an explicit opt-in (`withBodies`); without it, bodies are simply empty regardless
of whether php-parser is installed, and *with* it a missing parser throws (as does
`withBodies` on anonymous/internal classes and interfaces). Output therefore never
silently depends on whether php-parser happens to be present.

Two things stay **bound to their original context** and are the round-trip traps:

- **`self`/`static`/`parent` in types are kept verbatim.** Because they are
  keywords, nothing remaps them.
  `ClassManipulator::inheritMethod()`/`inheritProperty()`/`implement()` copy a
  member (via `Factory`) into a **different** class **without rewriting types or
  bodies**, so a copied `self::`/`parent`/`static` now silently refers to the
  wrong class.
- **Extracted bodies and defaults are lifted verbatim from the source**, relative
  to the **original** namespace. `Extractor` only tags names the parser
  *fully-qualified* (for later `simplifyType`); unqualified constants, `self::`,
  and local references are taken as written, so moving that code to another
  namespace can change its meaning.

## Validation is deferred to print time

Invariants (`abstract`+`final`, readonly without a type, abstract property
without an abstract hook, initialized property in an interface, enum case
values not matching the backing type, …) live in `validate()` methods
(`ClassType`, `InterfaceType`, `EnumType`, `Property`, `PropertyHook`,
`Method`, `Constant`, `PromotedParameter`) and are invoked by the **Printer**,
not by setters. The
model deliberately tolerates invalid intermediate states so objects can be
assembled in any order — `Factory` relies on this. **A new invariant belongs in
`validate()`, never in a setter.** The one exception is `setNullable()`
throwing without a type: that is an invariant of the API, not of the
generated code. The Printer also fills gaps at print time:
an enum with no `setType()` gets its backing type inferred from the first
case's scalar value.

## Navigation map

| Concern | Where |
|---|---|
| Type parsing, adding/removing null | `Helpers::createType`, `Helpers::nullableType` (`Type::nullable` for strings) |
| Per-class type API | `Parameter`/`Property`/`Constant`/`EnumType`, `Traits/FunctionLike` |
| Where nullability folds into the type | `applyNullable()` in `Parameter`/`Property`, `FunctionLike::setReturnNullable` |
| Short vs full name | `ClassLike::getName`/`getFullName`, `Factory::createClassObject` |
| Name resolution / simplification | `PhpNamespace::resolveName`/`simplifyName`/`simplifyType` |
| Tagged names in opaque strings | `Helpers::tagName`/`simplifyTaggedNames` |
| Case-insensitive member keys | `Traits/MethodsAware`, `PhpNamespace` |
| Deferred validation | `validate()` methods, called from `Printer` |
| Reflection vs source loading | `Factory`, `Extractor` |
| Member relocation trap | `ClassManipulator::inheritMethod`/`inheritProperty`/`implement` |
