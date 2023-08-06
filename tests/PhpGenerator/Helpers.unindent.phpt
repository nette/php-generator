<?php

/**
 * Test: Nette\PhpGenerator\Helpers::unindent()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::unindent('', 1));
Assert::same("\n", Helpers::unindent("\n", 1));
Assert::same('word', Helpers::unindent('word', 1));
Assert::same("\nword", Helpers::unindent("\nword", 1));
Assert::same("\nword\n", Helpers::unindent("\nword\n", 1));
Assert::same('word', Helpers::unindent("\tword", 1));
Assert::same("\tword", Helpers::unindent("\t\tword", 1));
Assert::same('word', Helpers::unindent("\t\tword", 2));
Assert::same("\nword", Helpers::unindent("\n\tword", 1));
Assert::same("word\t", Helpers::unindent("word\t", 1));
Assert::same("word\tword", Helpers::unindent("word\tword", 1));
Assert::same("word\t\nword", Helpers::unindent("word\t\nword", 1));
Assert::same('word', Helpers::unindent('    word', 1));
Assert::same('    word', Helpers::unindent('        word', 1));
