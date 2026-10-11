<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Models\Token;

use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpiredAtTest extends TestCase
{
    #[Test]
    #[DataProvider('isExpiredDataProvider')]
    public function isExpired(DateTimeImmutable $value, DateTimeInterface $now, bool $expected): void
    {
        $expiredAt = new ExpiredAt($value);

        $this->assertSame($expected, $expiredAt->isExpired($now));
    }

    public static function isExpiredDataProvider(): array
    {
        return [
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                new DateTimeImmutable('2019-12-09 12:00:01'),
                true,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                new DateTimeImmutable('2019-12-09 11:59:59'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                new DateTimeImmutable('2019-12-09 12:00:00'),
                false,
            ],
        ];
    }
}
