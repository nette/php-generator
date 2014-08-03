<?php

/**
 * Test: Nette\PhpGenerator for use clause
 */

use Nette\PhpGenerator\ClassType;
use Tester\Assert;

require __DIR__ . "/../bootstrap.php";

$class = new ClassType("Namespaced\\ClassName");
$class
	->addUse("Elasticsearch\\Client", NULL, $firstAlias)
	->addUse("Guzzle\\Http\\Client", NULL, $secondAlias)
	->addUse("Predis\\Client", NULL, $thirdAlias)
	->addUse("Jakubkulhan\\Autowiring\\Autowired", NULL, $autowiredAlias);

$class->addProperty("firstClient")
	->addDocument("@var {$firstAlias}")
	->addDocument("@{$autowiredAlias}");

$class->addProperty("secondClient")
	->addDocument("@var {$secondAlias}")
	->addDocument("@{$autowiredAlias}");

$class->addProperty("thirdClient")
	->addDocument("@var {$thirdAlias}")
	->addDocument("@{$autowiredAlias}");

$class->addUse("Elasticsearch\\Client");

Assert::throws(function () use ($class, $firstAlias) {
	$class->addUse("Elasticsearch\\Client", $firstAlias . "FooBar");
}, "Nette\\InvalidStateException");

Assert::matchFile(__DIR__ . "/ClassType.use.expect", (string)$class);
