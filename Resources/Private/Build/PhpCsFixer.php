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

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER' => true,
        '@DoctrineAnnotation' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        'no_superfluous_phpdoc_tags' => true,
    ])
    ->setFinder($finder)
;
