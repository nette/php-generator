<?php

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::tabsToSpaces(''));
Assert::same("\n  \n  ", Helpers::tabsToSpaces("\n  \n  "));
Assert::same("\n        a\n    b", Helpers::tabsToSpaces("\n\t\ta\n\tb"));
Assert::same("\n        a\n    b\n", Helpers::tabsToSpaces("\n\t\ta\n\tb\n"));
Assert::same("\n    a\n  b\n", Helpers::tabsToSpaces("\n\t\ta\n\tb\n", 2));
