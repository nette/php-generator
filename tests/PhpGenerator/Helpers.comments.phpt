<?php

/**
 * Test: Nette\PhpGenerator\Helpers::formatDocComment() & unformatDocComment()
 */

declare(strict_types=1);

use Nette\PhpGenerator\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same('', Helpers::formatDocComment(' '));
Assert::same("/** @var string */\n", Helpers::formatDocComment('@var string'));
Assert::same("/**\n * @var string\n */\n", Helpers::formatDocComment("@var string\n"));
Assert::same("/**\n * A\n * B\n * C\n */\n", Helpers::formatDocComment("A\nB\nC\n"));
Assert::same("/**\n * @var string\n */\n", Helpers::formatDocComment("@var string \r\n"));
Assert::same("/**\n * A\n *\n * B\n */\n", Helpers::formatDocComment("A\n\nB"));

Assert::same('', Helpers::unformatDocComment(''));
Assert::same('', Helpers::unformatDocComment("/**  */\n\r\t"));
Assert::same('@var string', Helpers::unformatDocComment(' /** @var string */ '));
Assert::same('@var string', Helpers::unformatDocComment("/**\n * @var string\n */"));
Assert::same("A\nB\nC", Helpers::unformatDocComment("/**\n * A\n * B\n * C\n */\n"));
