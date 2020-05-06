<?php

/**
 * Test: Nette\PhpGenerator\Helpers::(un)indentPhp()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::indentPhp('', -1));
Assert::same("\n", Helpers::indentPhp("\n", -1));
Assert::same('word', Helpers::indentPhp('word', -1));
Assert::same("\nword", Helpers::indentPhp("\nword", -1));
Assert::same("\nword\n", Helpers::indentPhp("\nword\n", -1));
Assert::same('word', Helpers::indentPhp("\tword", -1));
Assert::same("\tword", Helpers::indentPhp("\t\tword", -1));
Assert::same('word', Helpers::indentPhp("\t\tword", -2));
Assert::same("\nword", Helpers::indentPhp("\n\tword", -1));
Assert::same("word\t", Helpers::indentPhp("word\t", -1));
Assert::same("word\tword", Helpers::indentPhp("word\tword", -1));
Assert::same("word\t\nword", Helpers::indentPhp("word\t\nword", -1));
Assert::same('word', Helpers::indentPhp('    word', -1));
Assert::same('    word', Helpers::indentPhp('        word', -1));


same(<<<'XX'
$s = "a
	b" + "{${'
	'}}";
XX
, Helpers::indentPhp(<<<'XX'
	$s = "a
	b" + "{${'
	'}}";
XX
, -1));


same(<<<'XX'
$s = 'a
	b' + '';
XX
, Helpers::indentPhp(<<<'XX'
	$s = 'a
	b' + '';
XX
, -1));


same(<<<'XX'
// single
/*
multi
line */
/**
multi
line */
XX
, Helpers::indentPhp(<<<'XX'
	// single
	/*
	multi
	line */
	/**
	multi
	line */
XX
, -1));


same(<<<'XX'
?>
a
	b
	<?php
$c
XX
, Helpers::indentPhp(<<<'XX'
	?>
a
	b
	<?php
	$c
XX
, -1));


same(<<<'XX'
$s = <<<DOC
a
	b
DOC
;
XX
, Helpers::indentPhp(<<<'XX'
	$s = <<<DOC
a
	b
DOC
	;
XX
, -1));


same(<<<'XX'
$s = <<<'DOC'
a
	b
DOC
;
XX
, Helpers::indentPhp(<<<'XX'
	$s = <<<'DOC'
a
	b
DOC
	;
XX
, -1));
