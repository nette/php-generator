<?php

/**
 * Test: Nette\PhpGenerator for interfaces.
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$interface = new ClassType('IExample');
$interface
	->setType('interface')
	->addExtend('IOne')
	->addExtend('ITwo')
	->addComment('Description of interface');

$interface->addMethod('getForm');

Assert::matchFile(__DIR__ . '/ClassType.interface.expect', (string) $interface);
