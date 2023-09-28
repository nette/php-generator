<?php

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Printer;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

#[Attribute]
class MyAttribute
{
}


$printer = new Printer;

$classy = (new ClassType('Classy'))
  ->addAttribute(MyAttribute::class, [0]);

Assert::same('#[MyAttribute(0)]
class Classy
{
}
', $printer->printClass($classy));
