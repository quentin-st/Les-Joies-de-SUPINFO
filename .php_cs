<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('app')
    ->exclude('vendor')
    ->exclude('web')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_before_statement' => false,
        'braces' => ['allow_single_line_closure' => true],
        'heredoc_to_nowdoc' => false,
        'no_unreachable_default_argument_value' => false,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'trailing_comma_in_multiline_array' => false,
        'yoda_style' => null,
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder);
