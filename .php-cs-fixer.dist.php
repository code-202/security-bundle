<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude([
        'config',
        'public',
        'var',
        'vendor',
    ])
    ->notPath([
        'tests/bootstrap.php',
    ])
;

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setLineEnding("\n")
    ->setRules([
        '@PhpCsFixer' => true,
        'ordered_class_elements' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'phpdoc_separation' => [
            'skip_unlisted_annotations' => true,
        ],
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'try',
            ],
        ],
        'method_argument_space' => [
            'attribute_placement' => 'same_line',
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
    ])
    ;

