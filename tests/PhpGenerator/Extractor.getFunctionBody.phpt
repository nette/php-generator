<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$extractor = new Extractor('<?php
namespace NS;

function bar1()
{
	$a = 10;
	echo 123;
}

function bar2()
{
	echo "hello";
}
');

Assert::match(
	"\$a = 10;\necho 123;",
	$extractor->extractFunctionBody('NS\bar1')
);
