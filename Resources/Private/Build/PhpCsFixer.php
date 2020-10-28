<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__, 3);
require $baseDir.'/.Build/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in($baseDir.'/Classes')
    ->in($baseDir.'/Tests/Unit')
    ->in($baseDir.'/Configuration/TCA')
    ->in($baseDir.'/Configuration/SiteConfiguration')
    ->in($baseDir.'/Resources/Private/Build')
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP73Migration' => true,
        '@PHP71Migration:risky' => true,
    ])
    ->setFinder($finder)
;
