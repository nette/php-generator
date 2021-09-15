<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$extractor = new Extractor('<?php
namespace NS;

abstract class Foo
{
	function bar1()
	{
		$a = 10;
		echo 123;
	}

	function bar2()
	{
		echo "hello";
	}

	abstract function bar3();
}

abstract class Another
{
	function bar3()
	{
		echo 123;
	}
}
');

$bodies = $extractor->extractMethodBodies('NS\Undefined');
Assert::same([], $bodies);

$bodies = $extractor->extractMethodBodies('NS\Foo');
Assert::same([
	'bar1' => "\$a = 10;\necho 123;",
	'bar2' => 'echo "hello";',
], $bodies);
