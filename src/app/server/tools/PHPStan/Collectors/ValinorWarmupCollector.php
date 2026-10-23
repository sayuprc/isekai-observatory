<?php

declare(strict_types=1);

namespace Tools\PHPStan\Collectors;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * config/valinor.php に `Foo::class` で書かれた型を集める
 *
 * @implements Collector<ClassConstFetch, string>
 */
final class ValinorWarmupCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return ClassConstFetch::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): ?string
    {
        if (! str_ends_with($scope->getFile(), 'config/valinor.php')) {
            return null;
        }

        if (! $node->class instanceof Name || ! $node->name instanceof Identifier || $node->name->toLowerString() !== 'class') {
            return null;
        }

        return $scope->resolveName($node->class);
    }
}
