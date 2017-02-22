<?php

use Nette\PhpGenerator\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::true(Helpers::isNamespace('Item'));
Assert::true(Helpers::isNamespace("\x7F"));
Assert::true(Helpers::isNamespace("\x7F\\\x7F"));
Assert::false(Helpers::isNamespace('0Item'));
Assert::true(Helpers::isNamespace('Item\Item'));
Assert::false(Helpers::isNamespace('Item\\\\Item'));
Assert::false(Helpers::isNamespace('\\Item'));
Assert::false(Helpers::isNamespace('Item\\'));
