<?php

/**
 * Test: Nette\PhpGenerator for interfaces.
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$interface = new ClassType('IExample');
$interface
	->setType('interface')
	->addExtend('IOne')
	->addExtend('ITwo')
	->addComment('Description of interface');

Assert::same(['IOne', 'ITwo'], $interface->getExtends());

$interface->addMethod('getForm');

Assert::matchFile(__DIR__ . '/expected/ClassType.interface.expect', (string) $interface);
