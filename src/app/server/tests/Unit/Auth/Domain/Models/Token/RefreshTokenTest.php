<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Models\Token;

use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use EntityFactory;

    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(RefreshToken $object, RefreshToken $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                true,
            ],
            [
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                    'token-2',
                    new DateTimeImmutable('2026-01-02 00:00:00'),
                    ConsumptionStatus::Consumed->value,
                ),
                true,
            ],
            [
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                RefreshToken::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                false,
            ],
            [
                RefreshToken::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                    'token-1',
                    new DateTimeImmutable('2026-01-01 00:00:00'),
                    ConsumptionStatus::Unused->value,
                ),
                RefreshToken::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                    'token-2',
                    new DateTimeImmutable('2026-01-02 00:00:00'),
                    ConsumptionStatus::Consumed->value,
                ),
                false,
            ],
        ];
    }

    #[Test]
    #[DataProvider('isAvailableDataProvider')]
    public function isAvailable(
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
        DateTimeInterface $now,
        bool $expected,
    ): void {
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $this->generateUuid(),
            'token',
            $expiredAt,
            $status,
        );

        $this->assertSame($expected, $refreshToken->isAvailable($now));
    }

    public static function isAvailableDataProvider(): array
    {
        return [
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 11:59:59'),
                true,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 12:00:00'),
                true,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 12:00:01'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 11:59:59'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 12:00:00'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 12:00:01'),
                false,
            ],
        ];
    }
}
