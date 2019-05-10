<?php

declare(strict_types = 1);

$baseDir = \dirname(\dirname(\dirname(__DIR__)));
require $baseDir . '/.Build/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in($baseDir . '/Classes')
    ->in($baseDir . '/Tests/Unit')
    ->in($baseDir . '/Configuration/TCA')
    ->in($baseDir . '/Resources/Private/Build');

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@DoctrineAnnotation' => true,
        'no_leading_import_slash' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unused_imports' => true,
        'concat_space' => ['spacing' => 'one'],
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'single_quote' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'no_blank_lines_after_phpdoc' => true,
        'array_syntax' => ['syntax' => 'short'],
        'whitespace_after_comma_in_array' => true,
        'function_typehint_space' => true,
        'hash_to_slash_comment' => true,
        'no_alias_functions' => true,
        'lowercase_cast' => true,
        'no_leading_namespace_whitespace' => true,
        'native_function_casing' => true,
        'no_short_bool_cast' => true,
        'no_unneeded_control_parentheses' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_trim' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'return_type_declaration' => ['space_before' => 'none'],
        'cast_spaces' => ['space' => 'none'],
        'declare_equal_normalize' => ['space' => 'single'],
        'dir_constant' => true,
        'phpdoc_no_access' => true
    ])
    ->setFinder($finder);
