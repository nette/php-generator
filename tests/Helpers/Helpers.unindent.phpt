<?php declare(strict_types=1);

/**
 * Test: Nette\PhpGenerator\Helpers::unindent()
 */

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::unindent(''));
Assert::same("\n", Helpers::unindent("\n"));
Assert::same('word', Helpers::unindent('word'));
Assert::same("\nword", Helpers::unindent("\nword"));
Assert::same("\nword\n", Helpers::unindent("\nword\n"));
Assert::same('word', Helpers::unindent("\tword"));
Assert::same("\tword", Helpers::unindent("\t\tword"));
Assert::same('word', Helpers::unindent("\t\tword", 2));
Assert::same("\nword", Helpers::unindent("\n\tword"));
Assert::same("word\t", Helpers::unindent("word\t"));
Assert::same("word\tword", Helpers::unindent("word\tword"));
Assert::same("word\t\nword", Helpers::unindent("word\t\nword"));
Assert::same('word', Helpers::unindent('    word'));
Assert::same('    word', Helpers::unindent('        word'));
