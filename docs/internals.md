# PhpGenerator internals

How `nette/php-generator` handles the parts that are **not** obvious from the
object model, for agents editing it. Most of the package — `ClassType`,
`Method`, `Property`, the `Traits/*` — is a self-explanatory builder and does not
belong here. The expensive-to-reconstruct knowledge is concentrated in three
places — **type handling, the name/namespace model, and the reflection↔source
round-trip** — plus two cross-cutting contracts: tagged names and print-time
validation. One file.

## Type handling: three representations of nullability, and the Printer is where truth converges

A member's nullability is stored in up to **three** places at once, and they do
**not** agree:

1. **Inside the type string** — but only partially. `Helpers::validateType`
   strips a **leading `?`** and moves it to the flag (below); it does **not** touch
   `|null`. So `setType('?int')` stores `'int'` + flag, while `setType('int|null')`
   stores the literal `'int|null'` + **no** flag.
2. **A separate `$nullable` bool flag** (`setNullable`, or set by `validateType`
   from the `?`).
3. **Implicitly from a `null` default** — `Parameter::isNullable()` and
   `Property::isNullable()` return true when the default/initial value is `null`,
   even with no `?` and no flag. **Return types have no such inference** (flag
   only).

The consequences are real traps:

- **`setType('int|null')->isNullable()` is `false`; `setType('?int')->isNullable()`
  is `true`.** Same meaning, different answer, because `isNullable` reads the flag,
  not the string.
- **`getType(asObject: true)` builds `Nette\Utils\Type::fromString($this->type)`
  from the string alone** — the flag is not passed. So `?`/flag-based nullability
  is **lost** in the object form, while `|null` written into the string survives.

Nothing reconciles these until printing. **`Printer::printType($type, $nullable)`
is the single convergence point**: it simplifies the type against the namespace,
then `Type::nullable()` decides the final shape — `?` for a bare type, `|null`
appended for a union, an error for an intersection. This is the "it lies
elsewhere, the truth is only in the Printer" fact — do not try to normalize
nullability earlier.

## The type API is fragmented across five classes

`setType`/`getType` are **duplicated, not shared via a trait**, and differ:

| Class | `setType` validates? | `getType($asObject)`? | nullable flag? |
|---|---|---|---|
| `Parameter`, `Property`, function **return** | yes | yes | yes |
| `Constant` | validates but stores the string **verbatim** (keeps `?`) | **no `$asObject`** | **none** |
| `EnumType` | **no validation** (it is the enum *backing* type, int/string) | no | n/a |

So `Constant`'s nullability lives purely in its string and prints with
`nullable: false`; `EnumType::setType()` stores anything, and the backing type
is checked only by `EnumType::validate()` at print time. An agent
"unifying" type handling must account for these deliberate per-class differences.

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
`validate()`, never in a setter.** The Printer also fills gaps at print time:
an enum with no `setType()` gets its backing type inferred from the first
case's scalar value.

## Navigation map

| Concern | Where |
|---|---|
| Nullability normalization | `Helpers::validateType`, `Type::nullable` |
| Per-class type API | `Parameter`/`Property`/`Constant`/`EnumType`, `Traits/FunctionLike` |
| Type truth convergence | `Printer::printType` |
| Short vs full name | `ClassLike::getName`/`getFullName`, `Factory::createClassObject` |
| Name resolution / simplification | `PhpNamespace::resolveName`/`simplifyName`/`simplifyType` |
| Tagged names in opaque strings | `Helpers::tagName`/`simplifyTaggedNames` |
| Case-insensitive member keys | `Traits/MethodsAware`, `PhpNamespace` |
| Deferred validation | `validate()` methods, called from `Printer` |
| Reflection vs source loading | `Factory`, `Extractor` |
| Member relocation trap | `ClassManipulator::inheritMethod`/`inheritProperty`/`implement` |
