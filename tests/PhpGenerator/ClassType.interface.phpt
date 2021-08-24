<?php

/**
 * Test: Nette\PhpGenerator for interfaces.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$interface = ClassType::interface('IExample');
$interface
	->addExtend('IOne')
	->addExtend('ITwo')
	->addComment('Description of interface');

Assert::same(['IOne', 'ITwo'], $interface->getExtends());

$interface->addMethod('getForm');

sameFile(__DIR__ . '/expected/ClassType.interface.expect', (string) $interface);
