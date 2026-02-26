<?php declare(strict_types=1);

use Nette\PhpGenerator\PhpFile;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('file with singleLineOpenTag and strict types', function () {
	$file = new PhpFile;
	$file->addComment('This file is auto-generated. DO NOT EDIT!');
	$file->setStrictTypes();
	$file->addClass('A');

	$printer = new Nette\PhpGenerator\Printer;
	$printer->declareOnOpenTag = true;

	Assert::match(<<<'XX'
		<?php declare(strict_types=1);

		/**
		 * This file is auto-generated. DO NOT EDIT!
		 */

		class A
		{
		}

		XX, $printer->printFile($file));
});


test('singleLineOpenTag without comment', function () {
	$file = new PhpFile;
	$file->setStrictTypes();
	$file->addClass('A');

	$printer = new Nette\PhpGenerator\Printer;
	$printer->declareOnOpenTag = true;

	Assert::match(<<<'XX'
		<?php declare(strict_types=1);

		class A
		{
		}

		XX, $printer->printFile($file));
});


test('singleLineOpenTag has no effect without strict types', function () {
	$file = new PhpFile;
	$file->addClass('A');

	$printerA = new Nette\PhpGenerator\Printer;
	$printerB = new Nette\PhpGenerator\Printer;
	$printerB->declareOnOpenTag = true;

	Assert::same($printerA->printFile($file), $printerB->printFile($file));
});
