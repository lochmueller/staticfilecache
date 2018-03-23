<?php

declare(strict_types=1);
require __DIR__ . '/.Build/vendor/autoload.php';

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/Classes')
            ->in(__DIR__ . '/Tests/Unit')
            ->in(__DIR__ . '/Configuration/TCA')
    )
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP70Migration' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'general_phpdoc_annotation_remove' => [
            'annotations' => ['author', 'package', 'subpackage'],
        ],
        'linebreak_after_opening_tag' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'ordered_imports' => [
            'sortAlgorithm' => 'alpha',
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only',
        ],
        'no_null_property_initialization' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_homoglyph_names' => true,
        'declare_strict_types' => true,
        'mb_str_functions' => true,
        'native_function_invocation' => [
            'exclude' => [],
        ],
        'ordered_class_elements' => true,
        'no_short_echo_tag' => true,
        'no_superfluous_elseif' => true,
        'no_unreachable_default_argument_value' => true,
        'no_php4_constructor' => true,
        'simplified_null_return' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ]);