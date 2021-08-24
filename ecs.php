<?php

/**
 * Rules for Nette Coding Standard
 * https://github.com/nette/coding-standard
 */

declare(strict_types=1);


return function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
	$containerConfigurator->import(PRESET_DIR . '/php71.php');

	$parameters = $containerConfigurator->parameters();

	$parameters->set('skip', [
		'fixtures*/*',
		'tests/PhpGenerator/Dumper.dump().enum.phpt', // enum

		// constant NULL, FALSE
		PhpCsFixer\Fixer\Casing\LowercaseConstantsFixer::class => [
			'src/PhpGenerator/Type.php',
		],
	]);
};
