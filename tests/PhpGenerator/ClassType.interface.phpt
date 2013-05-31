<?php

/**
 * Test: Nette\PhpGenerator for interfaces.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\PhpGenerator\ClassType;


require __DIR__ . '/../bootstrap.php';


$interface = new ClassType('IExample');
$interface
	->setType('interface')
	->addExtend('IOne')
	->addExtend('ITwo')
	->addDocument('Description of interface');

$interface->addMethod('getForm');

Assert::match(file_get_contents(__DIR__ . '/ClassType.interface.expect'), (string) $interface);
