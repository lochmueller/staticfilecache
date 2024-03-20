<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$baseDir = dirname(__DIR__, 3);

require $baseDir . '/.Build/vendor/autoload.php';

$finder = Finder::create()
    ->in($baseDir)
    ->exclude('.Build')
    ->exclude('ext_tables.php')
    ->exclude('ext_localconf.php')
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP81Migration' => true,
        '@PHP80Migration:risky' => true,
        'no_superfluous_phpdoc_tags' => true,
    ])
    ->setFinder($finder)
;
