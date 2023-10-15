<?php

declare(strict_types=1);

use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Extractor;
use Nette\PhpGenerator\Literal;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


$file = (new Extractor(<<<'XX'
	<?php

	#[Attr(1, foo: 2, bar: new Attr(3))]
	class Class1
	{
		const Foo = [1];

		public $null = null;
		public $scalar = [true, false, 1, 1.0, 'hello'];
		public $const = [PHP_VERSION, self::Foo];
		public $array = [1, 2, ['x' => [3]], ...self::Foo];
		public $concat = 'x' . 'y';
		public $math = 10 * 3;

		public function foo($a = [1, 2, 3], $b = new stdClass(1, 2))
		{
		}
	}
	XX))->extractAll();


$class = $file->getClasses()['Class1'];
Assert::equal(
	new Attribute('Attr', [1, 'foo' => 2, 'bar' => new Literal('new /*(n*/\Attr(3)')]),
	$class->getAttributes()[0],
);

Assert::same([1], $class->getConstant('Foo')->getValue());

Assert::same(null, $class->getProperty('null')->getValue());
Assert::same(
	[true, false, 1, 1.0, 'hello'],
	$class->getProperty('scalar')->getValue(),
);
Assert::equal(
	[new Literal('/*(c*/\PHP_VERSION'), new Literal('self::Foo')],
	$class->getProperty('const')->getValue(),
);
Assert::equal(
	[1, 2, ['x' => [3]], new Literal('...self::Foo')],
	$class->getProperty('array')->getValue(),
);
Assert::equal(
	new Literal("'x' . 'y'"),
	$class->getProperty('concat')->getValue(),
);
Assert::equal(
	new Literal('10 * 3'),
	$class->getProperty('math')->getValue(),
);

$method = $class->getMethod('foo');
Assert::same(
	[1, 2, 3],
	$method->getParameter('a')->getDefaultValue(),
);
Assert::equal(
	new Literal('new /*(n*/\stdClass(1, 2)'),
	$method->getParameter('b')->getDefaultValue(),
);
