<?php

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::true(Helpers::isNamespaceIdentifier('Item'));
Assert::true(Helpers::isNamespaceIdentifier("\x7F"));
Assert::true(Helpers::isNamespaceIdentifier("\x7F\\\x7F"));
Assert::false(Helpers::isNamespaceIdentifier('0Item'));
Assert::true(Helpers::isNamespaceIdentifier('Item\Item'));
Assert::false(Helpers::isNamespaceIdentifier('Item\\\Item'));
Assert::false(Helpers::isNamespaceIdentifier('\Item'));
Assert::false(Helpers::isNamespaceIdentifier('Item\\'));

Assert::true(Helpers::isNamespaceIdentifier('\Item', allowLeadingSlash: true));
Assert::false(Helpers::isNamespaceIdentifier('Item\\', allowLeadingSlash: true));
