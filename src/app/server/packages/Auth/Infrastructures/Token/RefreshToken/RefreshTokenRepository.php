<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use DateTimeImmutable;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const string TABLE = 'refresh_tokens';

    /** @var list<string> */
    private const array COLUMNS = ['refresh_token_id', 'admin_user_id', 'token', 'expired_at', 'status'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
        private ClockInterface $clock,
    ) {
    }

    #[Override]
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('refresh_token_id', '=', $this->converter->toBin($refreshTokenId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        if (is_null($row)) {
            return null;
        }

        $hydrated = $this->hydrate($row);

        return $hydrated->isAvailable($this->clock->now()) ? $hydrated : null;
    }

    #[Override]
    public function save(RefreshToken $refreshToken): RefreshToken
    {
        $data = $refreshToken->toArray();
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['refresh_token_id', 'admin_user_id', 'token', 'expired_at', 'status', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($data['refresh_token_id']),
                $this->converter->toBin($data['admin_user_id']),
                $data['token'],
                $data['expired_at'],
                $data['status'],
                $now,
                $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        return $refreshToken;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): RefreshToken
    {
        return RefreshToken::reconstruct(
            $this->converter->toUuid(Row::string($row, 'refresh_token_id')),
            $this->converter->toUuid(Row::string($row, 'admin_user_id')),
            Row::string($row, 'token'),
            new DateTimeImmutable(Row::string($row, 'expired_at')),
            Row::int($row, 'status'),
        );
    }
}
