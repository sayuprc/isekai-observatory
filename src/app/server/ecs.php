<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

// 整形は mago fmt、lint で検出・修正できるものは mago lint が担う
// ここには mago で表現できないルールと、mago と結果が異なるルールだけを置く
// psr12 などの preset は mago fmt と書き換え合いになるので使わない
return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/packages',
        __DIR__ . '/public',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/tools',
        __DIR__ . '/artisan',
    ])
    ->withRootFiles()
    ->withSpacing(Option::INDENTATION_SPACES, PHP_EOL)
    ->withRules([
        \PhpCsFixer\Fixer\Basic\EncodingFixer::class,

        \PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer::class,
        \PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer::class,
        \PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer::class,

        \PhpCsFixer\Fixer\ClassNotation\ModifierKeywordsFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer::class,

        \PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer::class,
        \PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer::class,
        \PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer::class,

        \PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\ElseifFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\SimplifiedIfReturnFixer::class,

        \PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer::class,
        \PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer::class,

        \PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer::class,
        \PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer::class,

        \PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer::class,
        \PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer::class,

        \PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class,

        \PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocArrayTypeFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTagCasingFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer::class,

        \PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer::class,
        \PhpCsFixer\Fixer\Strict\StrictComparisonFixer::class,

        \PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer::class,

        \PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer::class,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class, [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
            'trait_import' => 'none',
            'case' => 'one',
        ],
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer::class, [
        'elements' => [
            'property',
        ],
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class, [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => null,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\FunctionNotation\MultilinePromotedPropertiesFixer::class, [
        'minimum_number_of_parameters' => 2,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class, [
        'import_symbols' => true,
        'leading_backslash_in_global_namespace' => true,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer::class, [
        'remove_inheritdoc' => true,
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer::class, [
        'null_adjustment' => 'always_last',
        'sort_algorithm' => 'none',
    ])
    ->withConfiguredRule(\PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class, [
        'tokens' => [
            'attribute',
            'break',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use',
            'use_trait',
        ],
    ])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',

        \PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class => [
            __DIR__ . '/ecs.php',
            __DIR__ . '/bootstrap/providers.php',
            __DIR__ . '/config/valinor.php',
        ],
    ]);
