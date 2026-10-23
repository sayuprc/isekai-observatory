<?php

declare(strict_types=1);

namespace Tools\PHPStan\Rules;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tools\PHPStan\Collectors\MapperSignatureCollector;
use Tools\PHPStan\Collectors\ValinorWarmupCollector;

/**
 * `MapperInterface::map()` に `Foo::class` を渡す型が、config/valinor.php の warmup に載っていることを確かめる
 *
 * 載っていないと、キャッシュが有効な環境で初回リクエストごとに型を解析することになる
 *
 * 外部ファイルを直接読まず Collector で集めるのは、config/valinor.php だけを直したときも PHPStan の結果キャッシュが古くならないようにするため
 * config/valinor.php が解析対象に無いときは、すべてを未登録と誤検知しないよう何も報告しない
 *
 * @implements Rule<CollectedDataNode>
 */
final class ValinorWarmupRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $warmup = [];

        foreach ($node->get(ValinorWarmupCollector::class) as $classes) {
            foreach ($classes as $class) {
                $warmup[$class] = true;
            }
        }

        if ($warmup === []) {
            return [];
        }

        $errors = [];

        foreach ($node->get(MapperSignatureCollector::class) as $file => $signatures) {
            foreach ($signatures as [$class, $line]) {
                if (isset($warmup[$class])) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(sprintf('%s が config/valinor.php の warmup に載っていません', $class))
                    ->file($file)
                    ->line($line)
                    ->identifier('valinor.warmupMissing')
                    ->build();
            }
        }

        return $errors;
    }
}
