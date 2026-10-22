<?php declare(strict_types=1);

use Nette\PhpGenerator\Constant;

require __DIR__ . '/../bootstrap.php';


test('final protected constant is fine', function () {
	$const = (new Constant('FOO'))
		->setValue(1)
		->setFinal()
		->setProtected();
	$const->validate();
});


testException('constant cannot be final and private', function () {
	$const = (new Constant('FOO'))
		->setValue(1)
		->setFinal()
		->setPrivate();
	$const->validate();
}, Nette\InvalidStateException::class, 'Constant FOO cannot be final and private at the same time.');
