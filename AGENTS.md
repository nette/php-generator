# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Documentation

Any distilled, agent-facing documentation for this package - how it works
internally and the rationale behind key design decisions - lives in `docs/`.
Consult it before non-trivial changes; it is the source of truth from which the
public manual is distilled.

The object model is a self-explanatory builder, but three areas are trap-rich and
expensive to reconstruct: **type/nullability handling, the name/namespace model,
and the reflection<->source round-trip.** Read `docs/internals.md` before touching them.

`docs/upgrading.md` records, per released version, every change visible to
users of the package (renames, removals, deprecations, signatures, behavior,
configuration), each with its commit hash; internal changes are not in it. It
is the place to look up the history of a public symbol. Do not write to it; it
is extended at release time from the git history.

## Project Overview

Nette PHP Generator programmatically generates PHP code (classes, functions,
namespaces, whole files) with support for modern features (property hooks, enums,
attributes, asymmetric visibility), plus loading existing code back into the object
model.

- **PHP Version**: 8.1 - 8.6 (v4.2)
- **Package**: `nette/php-generator`

## Essential Commands

```bash
# Run all tests
vendor/bin/tester tests -s        # or: composer tester
vendor/bin/tester tests/Printer -s     # one topic folder or a single .phpt

# Static analysis
composer phpstan
```

## Conventions

- Every file starts with `declare(strict_types=1);`; **tabs**; braces on the next
  line for functions/methods; Nette Coding Standard.
- Tests are Nette Tester `.phpt` grouped by topic under `tests/` (`ClassLike/`,
  `Members/`, `Factory/`, `Extractor/`, `Printer/`, `PhpFile/`, `Dumper/`,
  `Helpers/`); many compare against a `.expect` file in the topic's `expected/`
  subfolder via the `sameFile()` helper (keeps test files clean and makes output
  changes visible in diffs).

## Working in this repo

- **Nullability is stored in up to three disagreeing places** - inside the type
  string (a leading `?` is stripped to a flag, but `|null` stays literal), a
  separate `$nullable` flag, and implicitly from a `null` default (params/properties
  only, not return types). So `setType('int|null')->isNullable()` is `false` while
  `setType('?int')` is `true`. **The truth converges only in `Printer::printType`** -
  don't try to normalize nullability earlier.
- **The type API is duplicated across five classes, not shared** - `Constant` stores
  the type string verbatim with no `$asObject`/flag, `EnumType` skips validation
  entirely. Account for these deliberate differences before "unifying" them.
- **`getName()` returns the SHORT name and `getFullName()` the FQN** - the opposite
  of `ReflectionClass::getName()`. `PhpNamespace` is the resolution engine
  (`resolveName`/`simplifyName`/`simplifyType`), with separate alias tables per
  symbol kind.
- **Names inside bodies, attribute args, and dumped values survive only as invisible
  `/*(n*/` comment tags** (`Helpers::tagName`, resolved by `simplifyTaggedNames` at
  print time); an untagged FQN prints verbatim and is never simplified.
- **Loading code has two paths:** `Factory` (reflection only, no I/O) and `Extractor`
  (nikic/php-parser, reached via `withBodies` or the `fromCode()` path, and **throws**
  if the parser is absent - fail-fast, never silent degradation).
- **`self`/`parent`/`static` are kept verbatim** (they're keywords). So
  `ClassManipulator::inheritMethod()`/`inheritProperty()`/`implement()` copy a member
  into a different class **without rewriting** those references - a relocation trap.
  Extracted bodies are likewise relative to their original namespace.
- User-facing how-to (builder API, body placeholders, Printer configuration,
  property hooks, asymmetric visibility, arrow functions) is manual material and
  lives in the public web docs, not here.
