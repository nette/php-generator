<?php

/**
 * Test: Nette\PhpGenerator - PHP 8.4 property hooks for classes
 */

declare(strict_types=1);

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Tester\Assert;
use Nette\PhpGenerator\Closure;

require __DIR__ . '/../bootstrap.php';

$file = new PhpFile;
$file->setStrictTypes();

$class = new ClassType('Locale');

$class->addProperty('languageCode')
    ->setType('string')
    ->setPublic();

$countryCodeSetHookClosure = (new Closure)
    ->setBody('$this->countryCode = strtoupper($countryCode);');

$countryCodeSetHookClosure->addParameter('countryCode')->setType('string');

$class->addProperty('countryCode')
    ->setType('string')
    ->setValue('AA')
    ->setPublic()
    ->setSetHook($countryCodeSetHookClosure);

$combinedCodeGetHookClosure = (new Closure)
    ->setBody('return \sprintf("%s_%s", $this->languageCode, $this->countryCode);');

$combinedCodeSetHookClosure = (new Closure)
    ->setBody('[$this->languageCode, $this->countryCode] = explode(\'_\', $value, 2);');

$combinedCodeSetHookClosure->addParameter('value')->setType('string');

$class->addProperty('combinedCode')
    ->setType('string')
    ->setPublic()
    ->setGetHook($combinedCodeGetHookClosure)
    ->setSetHook($combinedCodeSetHookClosure);

$expected = <<<'PHP'
class Locale
{
    public string $languageCode;

    public string $countryCode = 'AA' {
        set (string $countryCode) {
            $this->countryCode = strtoupper($countryCode);
        }
    }

    public string $combinedCode {
        get {
            return \sprintf("%s_%s", $this->languageCode, $this->countryCode);
        }
        set (string $value) {
            [$this->languageCode, $this->countryCode] = explode('_', $value, 2);
        }
    }
}
PHP;

dump((new PsrPrinter)->printClass($class));

same(rtrim($expected), rtrim((new PsrPrinter)->printClass($class)));
