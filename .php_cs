<?php

require __DIR__ . '/.Build/vendor/autoload.php';

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/Classes')
    )
    ->setRules(array(
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP70Migration' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'general_phpdoc_annotation_remove' => [
            'annotations' => ['author', 'package'],
        ],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'ordered_imports' => [
            'sortAlgorithm' => 'alpha',
        ],
        'no_useless_else' => true,
        'no_homoglyph_names' => true,
        'declare_strict_types' => true,
        'mb_str_functions' => true,
        'no_php4_constructor' => true,
        'simplified_null_return' => true,
        'strict_comparison' => true,
    ));
