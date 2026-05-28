<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ergebnis\License;
use Ergebnis\PhpCsFixer\Config;
use PhpCsFixerCustomFixers\Fixer;

$license = License\Type\MIT::markdown(
    __DIR__ . '/LICENSE.md',
    License\Range::since(
        License\Year::fromString('2024'),
        new \DateTimeZone('UTC'),
    ),
    License\Holder::fromString('AIPTU'),
    License\Url::fromString('https://github.com/AIPTU/Smaccer'),
);
$license->save();

$ruleSet = Config\RuleSet\Php85::create()
    //->withHeader($license->header())
    ->withCustomFixers(Config\Fixers::fromFixers(
        new Fixer\CommentSurroundedBySpacesFixer(),
        new Fixer\CommentedOutFunctionFixer(),
        new Fixer\ConstructorEmptyBracesFixer(),
        new Fixer\EmptyFunctionBodyFixer(),
        new Fixer\NoCommentedOutCodeFixer(),
        new Fixer\NoDoctrineMigrationsGeneratedCommentFixer(),
        new Fixer\NoImportFromGlobalNamespaceFixer(),
        new Fixer\NoLeadingSlashInGlobalNamespaceFixer(),
        new Fixer\NoNullableBooleanTypeFixer(),
        new Fixer\NoPhpStormGeneratedCommentFixer(),
        new Fixer\NoReferenceInFunctionDefinitionFixer(),
        new Fixer\NoSuperfluousConcatenationFixer(),
        new Fixer\NoTrailingCommaInSinglelineFixer(),
        new Fixer\NoUselessCommentFixer(),
        new Fixer\NoUselessDirnameCallFixer(),
        new Fixer\NoUselessDoctrineRepositoryCommentFixer(),
        new Fixer\NoUselessParenthesisFixer(),
        new Fixer\NoUselessStrlenFixer(),
        new Fixer\PhpdocNoIncorrectVarAnnotationFixer(),
        new Fixer\PhpdocNoSuperfluousParamFixer(),
        new Fixer\PhpdocParamTypeFixer(),
        new Fixer\PhpdocSelfAccessorFixer(),
        new Fixer\PhpdocSingleLineVarFixer(),
        new Fixer\PhpdocTypesTrimFixer(),
        new Fixer\PromotedConstructorPropertyFixer(),
        new Fixer\ReadonlyPromotedPropertiesFixer(),
        new Fixer\SingleSpaceAfterStatementFixer(),
        new Fixer\StringableInterfaceFixer(),
    ))
    ->withRules(Config\Rules::fromArray([
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only'
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'declare'
            ]
        ],
        'blank_line_between_import_groups' => false,
        'concat_space' => [
            'spacing' => 'one'
        ],
        'braces_position' => [
            'classes_opening_brace' => 'same_line',
            'functions_opening_brace' => 'same_line',
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        "date_time_immutable" => false,
        'error_suppression' => [
            'noise_remaining_usages' => false,
        ],
        'final_class' => false,
        'final_public_method_for_abstract_class' => false,
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
            'import_classes' => null,
        ],
        'header_comment' => [
            'comment_type' => 'comment',
            'header' => trim($license->header()),
            'location' => 'after_open',
        ],
        'mb_str_functions' => false,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'native_constant_invocation' => [
            'scope' => 'namespaced'
        ],
        'native_function_invocation' => [
            'scope' => 'namespaced',
            'include' => ['@all'],
        ],
        'new_with_parentheses' => [
            'named_class' => true,
            'anonymous_class' => false,
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha'
        ],
        'ordered_class_elements' => false,
        'phpdoc_align' => [
            'align' => 'vertical',
            'tags' => [
                'param',
            ]
        ],
        'phpdoc_line_span' => [
            'property' => 'single',
            'method' => null,
            'const' => null
        ],
        'phpdoc_list_type' => false,
        'phpdoc_order' => [
            'order' => [
                'param',
                'return',
                'throws',
            ],
        ],
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'alpha',
        ],
        'return_type_declaration' => [
            'space_before' => 'one'
        ],
        'simplified_if_return' => true,
        'single_line_empty_body' => true,
        'static_lambda' => false,
        'strict_param' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => false,
            'elements' => [
                'arrays',
            ],
        ],
        'use_arrow_functions' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ]));

$config = Config\Factory::fromRuleSet($ruleSet)
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setCacheFile(__DIR__ . '/.build/php-cs-fixer/.php-cs-fixer.cache');
$config->getFinder()
    ->in(__DIR__ . '/src');

return $config;