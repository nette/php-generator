<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$extractor = new Extractor(<<<'XX'
	<?php
	function quoted()
	{
		$s = '
	a
		b"\'
			c
	';

		$d = "
	a
		b\"'
			c
	";

		$id = "
	a
		{$b}
			$c
	";
	}


	function heredoc()
	{
		$s1 = <<<DOC
	a
		{$b}
			$c
	DOC;

		$s2 = <<<DOC
		a
			{$b}
				$c x
		DOC;

		$s3 = <<<DOC
			a
				{$b}
					$c x
			DOC;

		$s4 = <<<DOC
		a
			{$b(<<<IN
	x  {$d("
		$x
	")}
	IN)}
			$c xx
			yy
		DOC;
	}


	function nowdoc()
	{
		$s1 = <<<'DOC'
	a
		b
			c 'q1' "q2"
	DOC;

		$s2 = <<<'DOC'
		a
			b
				c
		DOC;

		$s3 = <<<'DOC'
			a
				b
					c
			DOC;
	}

	XX);


Assert::match(
	<<<'XX'
		$s = "\na\n\tb\"'\n\t\tc\n";

		$d = "\na\n\tb\"'\n\t\tc\n";

		$id = "\na\n\t{$b}\n\t\t$c\n";
		XX,
	$extractor->extractFunctionBody('quoted'),
);


Assert::match(
	<<<'XX'
		$s1 = <<<DOC
		a
			{$b}
				$c
		DOC;

		$s2 = <<<DOC
		a
			{$b}
				$c x
		DOC;

		$s3 = <<<DOC
			a
				{$b}
					$c x
			DOC;

		$s4 = <<<DOC
		a
			{$b(<<<IN
		x  {$d("\n\t$x\n")}
		IN)}
			$c xx
			yy
		DOC;
		XX,
	$extractor->extractFunctionBody('heredoc'),
);


Assert::match(
	<<<'XX'
		$s1 = <<<'DOC'
		a
			b
				c 'q1' "q2"
		DOC;

		$s2 = <<<'DOC'
		a
			b
				c
		DOC;

		$s3 = <<<'DOC'
			a
				b
					c
			DOC;
		XX,
	$extractor->extractFunctionBody('nowdoc'),
);
