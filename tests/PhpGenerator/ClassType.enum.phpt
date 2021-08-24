<?php

/**
 * Test: Nette\PhpGenerator for enum.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$enum = ClassType::enum('Suit');

Assert::true($enum->isEnum());

$enum
	->setTraits(['ObjectTrait'])
	->addComment("Description of class.\nThis is example\n")
	->addAttribute('ExampleAttribute')
	->addConstant('ACTIVE', false);

$enum->addMethod('foo')
	->setBody('return 10;');

$enum->addCase('Clubs')
	->addComment('♣')
	->addAttribute('ValueAttribute');
$enum->addCase('Diamonds')
	->addComment('♦');
$enum->addCase('Hearts');
$enum->addCase('Spades');

$res[] = $enum;


$enum = ClassType::enum('Method');
$enum->addImplement('IOne');

$enum->addCase('GET', 'get');
$enum->addCase('POST', 'post');

$res[] = $enum;

sameFile(__DIR__ . '/expected/ClassType.enum.expect', implode("\n", $res));
