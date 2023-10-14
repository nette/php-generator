<?php

declare(strict_types=1);

use Nette\PhpGenerator\Extractor;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';


$file = (new Extractor(<<<'XX'
	<?php

	/** doc comment */
	class Class1
	{
	}

	XX))->extractAll();

Assert::null($file->getComment());
Assert::same('doc comment', $file->getClasses()['Class1']->getComment());


$file = (new Extractor(<<<'XX'
	<?php

	/** doc comment */

	namespace Abc;
	XX))->extractAll();

Assert::same('doc comment', $file->getComment());


$file = (new Extractor(<<<'XX'
	<?php

	#[ExampleAttribute]

	function () {};
	XX))->extractAll();

Assert::null($file->getComment());
