<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\RecoveryCode;

use Auth\Infrastructures\RecoveryCode\RandomRecoveryCodeGenerator;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class RandomRecoveryCodeGeneratorTest extends TestCase
{
    private RandomRecoveryCodeGenerator $generator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new RandomRecoveryCodeGenerator(new Randomizer());
    }

    #[Test]
    public function generatesCodeInExpectedFormat(): void
    {
        // 紛らわしい 0/O/1/I/L を除外した文字集合のみ、4-4 のハイフン区切り
        for ($i = 0; $i < 100; $i++) {
            $this->assertMatchesRegularExpression('/\A[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{4}-[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{4}\z/', $this->generator->generate());
        }
    }

    #[Test]
    public function usesInjectedRandomizer(): void
    {
        $generator = new RandomRecoveryCodeGenerator(new Randomizer(new Mt19937(1)));

        // 同じシードの決定的エンジンなら、生成結果は再現する
        $this->assertSame(
            new RandomRecoveryCodeGenerator(new Randomizer(new Mt19937(1)))->generate(),
            $generator->generate(),
        );
    }
}
