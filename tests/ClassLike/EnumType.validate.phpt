<?php declare(strict_types=1);

use Nette\PhpGenerator\{EnumType, Literal};

require __DIR__ . '/../bootstrap.php';


test('valid enums pass validation', function () {
	// pure enum
	$enum = new EnumType('Suit');
	$enum->addCase('Clubs');
	$enum->addCase('Diamonds');
	$enum->validate();

	// backed enum with consistent values
	$enum = new EnumType('Suit');
	$enum->addCase('Clubs', 1);
	$enum->addCase('Diamonds', 2);
	$enum->validate();

	// explicit backing type with matching values
	$enum = new EnumType('Suit');
	$enum->setType('string');
	$enum->addCase('Clubs', 'c');
	$enum->validate();

	// type names are case-insensitive
	$enum = new EnumType('Suit');
	$enum->setType('INT');
	$enum->addCase('Clubs', 1);
	$enum->validate();

	// literal values are not checked against the backing type
	$enum = new EnumType('Suit');
	$enum->setType('int');
	$enum->addCase('Clubs', new Literal('self::BASE + 1'));
	$enum->validate();
});


testException('backing type must be int or string', function () {
	$enum = new EnumType('Suit');
	$enum->setType('float');
	$enum->validate();
}, Nette\InvalidStateException::class, "Enum 'Suit': backing type must be int or string, 'float' given.");


testException('case of explicitly backed enum must have a value', function () {
	$enum = new EnumType('Suit');
	$enum->setType('int');
	$enum->addCase('Clubs', 1);
	$enum->addCase('Diamonds');
	$enum->validate();
}, Nette\InvalidStateException::class, "Case 'Diamonds' of backed enum 'Suit' must have a value.");


testException('case of implicitly backed enum must have a value', function () {
	$enum = new EnumType('Suit');
	$enum->addCase('Clubs', 1);
	$enum->addCase('Diamonds');
	$enum->validate();
}, Nette\InvalidStateException::class, "Case 'Diamonds' of backed enum 'Suit' must have a value.");


testException('value type must match inferred backing type', function () {
	$enum = new EnumType('Suit');
	$enum->addCase('Clubs', 1);
	$enum->addCase('Diamonds', 'd');
	$enum->validate();
}, Nette\InvalidStateException::class, "Case 'Diamonds' of enum 'Suit': value type string does not match backing type int.");


testException('value type must match explicit backing type', function () {
	$enum = new EnumType('Suit');
	$enum->setType('string');
	$enum->addCase('Clubs', 1);
	$enum->validate();
}, Nette\InvalidStateException::class, "Case 'Clubs' of enum 'Suit': value type int does not match backing type string.");


testException('backing type cannot be inferred from literal values only', function () {
	$enum = new EnumType('Suit');
	$enum->addCase('Clubs', new Literal('self::BASE'));
	$enum->validate();
}, Nette\InvalidStateException::class, "Enum 'Suit': backing type cannot be inferred from case values, set it explicitly using setType().");
