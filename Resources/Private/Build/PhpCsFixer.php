<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$baseDir = dirname(__DIR__, 3);

require $baseDir.'/.Build/vendor/autoload.php';

$finder = Finder::create()
    ->in($baseDir.'/Classes')
    ->in($baseDir.'/Tests/Unit')
    ->in($baseDir.'/Configuration')
    ->in($baseDir.'/Resources/Private/Build')
;

return (new Config())
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
