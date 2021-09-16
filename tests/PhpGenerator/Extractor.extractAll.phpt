<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.php')))->extractAll();
Assert::type(Nette\PhpGenerator\PhpFile::class, $file);
sameFile(__DIR__ . '/expected/Factory.fromCode.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.74.php')))->extractAll();
sameFile(__DIR__ . '/expected/Factory.fromCode.74.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.80.php')))->extractAll();
sameFile(__DIR__ . '/expected/Factory.fromCode.80.expect', (string) $file);

//$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/classes.81.php')))->extractAll();
//sameFile(__DIR__ . '/expected/Factory.fromCode.81.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/enum.php')))->extractAll();
sameFile(__DIR__ . '/expected/Factory.fromCode.enum.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/traits.php')))->extractAll();
sameFile(__DIR__ . '/expected/Factory.fromCode.traits.expect', (string) $file);

$file = (new Extractor(file_get_contents(__DIR__ . '/fixtures/bodies.php')))->extractAll();
sameFile(__DIR__ . '/expected/Factory.fromCode.bodies.expect', (string) $file);

$file = (new Extractor(
	<<<'XX'
<?php
class Class1
{
	public function foo()
	{
		new class {
			function bar() {
			}
		};
	}
}

function () {};

/** doc */
function foo(A $a): B|C
{
	function bar()
	{
	}
}

XX
))->extractAll();
Assert::type(Nette\PhpGenerator\PhpFile::class, $file);
Assert::match(<<<'XX'
<?php

class Class1
{
	public function foo()
	{
		new class {
			function bar() {
			}
		};
	}
}

/**
 * doc
 */
function foo(A $a): B|C
{
	function bar()
	{
	}
}
XX
, (string) $file);


Assert::exception(function () {
	(new Extractor(''));
}, Nette\InvalidStateException::class, 'The input string is not a PHP code.');
