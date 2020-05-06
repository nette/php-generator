<?php

/**
 * Test: Nette\PhpGenerator\Helpers::indentPhp()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::indentPhp(''));
Assert::same("\n", Helpers::indentPhp("\n"));
Assert::same("\tword", Helpers::indentPhp('word'));
Assert::same("\n\tword", Helpers::indentPhp("\nword"));
Assert::same("\n\tword\n", Helpers::indentPhp("\nword\n"));
Assert::same("\n\t\tword\n", Helpers::indentPhp("\nword\n", 2));
Assert::same("\n        word\n", Helpers::indentPhp("\nword\n", 2, '    '));

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
));


same(<<<'XX'
	$s = 'a
b' + '';
XX
, Helpers::indentPhp(<<<'XX'
$s = 'a
b' + '';
XX
));


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
));


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
));


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
));


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
));
