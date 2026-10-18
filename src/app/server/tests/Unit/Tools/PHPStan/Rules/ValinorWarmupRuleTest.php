<?php

declare(strict_types=1);

namespace Tests\Unit\Tools\PHPStan\Rules;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tools\PHPStan\Collectors\MapperSignatureCollector;
use Tools\PHPStan\Collectors\ValinorWarmupCollector;
use Tools\PHPStan\Rules\ValinorWarmupRule;

/**
 * @extends RuleTestCase<ValinorWarmupRule>
 */
class ValinorWarmupRuleTest extends RuleTestCase
{
    private const string UNREGISTERED = 'Tests\Unit\Tools\PHPStan\Rules\Data\ValinorWarmup\Unregistered が config/valinor.php の warmup に載っていません';

    #[Test]
    public function reportsSignaturesPassedToMapperButNotInWarmup(): void
    {
        $this->analyse([
            __DIR__ . '/data/valinor-warmup/calls.php',
            __DIR__ . '/data/valinor-warmup/config/valinor.php',
        ], [
            [self::UNREGISTERED, 31],
            [self::UNREGISTERED, 32],
        ]);
    }

    #[Test]
    public function reportsNothingWhenConfigIsNotAnalysed(): void
    {
        $this->analyse([__DIR__ . '/data/valinor-warmup/calls.php'], []);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new ValinorWarmupRule();
    }

    #[Override]
    protected function getCollectors(): array
    {
        return [new MapperSignatureCollector(), new ValinorWarmupCollector()];
    }
}
