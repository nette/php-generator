<?php

declare(strict_types=1);

use Nette\PhpGenerator\InterfaceType;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$interface = new InterfaceType('IExample');
$interface
	->addExtend('IOne')
	->addExtend('ITwo')
	->addComment('Description of interface');

Assert::same(['IOne', 'ITwo'], $interface->getExtends());

$interface->addMethod('getForm');

sameFile(__DIR__ . '/expected/InterfaceType.expect', (string) $interface);
