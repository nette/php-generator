<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$extractor = new Extractor(<<<'XX'
	<?php
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

	enum Color
	{
		case Red;
		case Blue;

		public function getName(): string
		{
			return $this->name;
		}
	}

	XX);

$bodies = $extractor->extractMethodBodies('NS\Undefined');
Assert::same([], $bodies);

$bodies = $extractor->extractMethodBodies('NS\Foo');
Assert::same([
	'bar1' => "\$a = 10;\necho 123;",
	'bar2' => 'echo "hello";',
], $bodies);

$bodies = $extractor->extractMethodBodies('NS\Color');
Assert::same([
	'getName' => 'return $this->name;',
], $bodies);
