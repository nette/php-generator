<?php

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::true(Helpers::isIdentifier('Item'));
Assert::true(Helpers::isIdentifier("\x7F"));
Assert::false(Helpers::isIdentifier('0Item'));
Assert::false(Helpers::isIdentifier('Item\Item'));
