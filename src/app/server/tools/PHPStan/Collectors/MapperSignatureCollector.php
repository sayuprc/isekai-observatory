<?php

declare(strict_types=1);

namespace Tools\PHPStan\Collectors;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Type\ObjectType;
use Support\Contracts\MapperInterface;

/**
 * `MapperInterface::map()` に `Foo::class` で渡している型と、その行を集める
 *
 * @implements Collector<MethodCall, array{string, int}>
 */
final class MapperSignatureCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return ?array{string, int}
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $node->name instanceof Identifier || $node->name->toString() !== 'map') {
            return null;
        }

        if (! new ObjectType(MapperInterface::class)->isSuperTypeOf($scope->getType($node->var))->yes()) {
            return null;
        }

        $signature = $node->getArgs()[0]->value ?? null;

        if (! $signature instanceof ClassConstFetch
            || ! $signature->class instanceof Name
            || ! $signature->name instanceof Identifier
            || $signature->name->toLowerString() !== 'class') {
            return null;
        }

        return [$scope->resolveName($signature->class), $node->getStartLine()];
    }
}
