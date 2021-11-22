<?php

declare(strict_types=1);

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


function same(string $expected, $actual): void
{
	$expected = str_replace(PHP_EOL, "\n", $expected);
	Tester\Assert::same($expected, $actual);
}


function sameFile(string $file, $actual): void
{
	try {
		same(file_get_contents($file), $actual);
	} catch (Tester\AssertException $e) {
		$e->outputName = basename($file, '.expect');
		throw $e;
	}
}


Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
