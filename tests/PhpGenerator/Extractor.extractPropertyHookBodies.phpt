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
		public string $short {
			get => 'x';
		}

		public string $full {
			get {
				if (true) {
					return 'x';
				} else {
					return 'y';
				}
			}
		}

		public string $empty {
			set { }
		}

		abstract public string $abstract { get; }
	}

	XX);

$bodies = $extractor->extractPropertyHookBodies('NS\Undefined');
Assert::same([], $bodies);

$bodies = $extractor->extractPropertyHookBodies('NS\Foo');
Assert::same([
	'short' => ['get' => ["'x'", true]],
	'full' => [
		'get' => ["if (true) {\n	return 'x';\n} else {\n	return 'y';\n}", false],
	],
	'empty' => ['set' => ['', false]],
], $bodies);
