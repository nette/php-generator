<?php

/**
 * Test: Nette\Utils\PhpGenerator for interfaces.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\PhpGenerator\ClassType;



require __DIR__ . '/../bootstrap.php';



$interface = new ClassType('IExample');
$interface
	->setType('interface')
	->addExtend('IOne')
	->addExtend('ITwo')
	->addDocument('Description of interface');

$interface->addMethod('getForm');

Assert::match(file_get_contents(__DIR__ . '/PhpGenerator.interface.expect'), (string) $interface);
