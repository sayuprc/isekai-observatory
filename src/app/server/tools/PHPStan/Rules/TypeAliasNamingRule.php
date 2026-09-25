<?php

declare(strict_types=1);

namespace Tools\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * `@phpstan-type` と `@phpstan-import-type ... as` で定義する型エイリアス名を `_lowerCamel` に揃える
 *
 * @implements Rule<InClassNode>
 */
final class TypeAliasNamingRule implements Rule
{
    private const string PATTERN = '/\A_[a-z][a-zA-Z0-9]*\z/';

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $phpDoc = $node->getClassReflection()->getResolvedPhpDoc();

        if ($phpDoc === null) {
            return [];
        }

        $names = [
            ...array_keys($phpDoc->getTypeAliasTags()),
            ...array_keys($phpDoc->getTypeAliasImportTags()),
        ];

        $errors = [];

        foreach ($names as $name) {
            if (preg_match(self::PATTERN, (string)$name) === 1) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('型エイリアス名 %s は _lowerCamel 形式にしてください', $name))
                ->identifier('typeAlias.naming')
                ->build();
        }

        return $errors;
    }
}
