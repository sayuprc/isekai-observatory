<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use Auth\Domain\Services\RecoveryCode\RandomRecoveryCodeGeneratorInterface;
use Override;
use Random\Randomizer;

readonly class RandomRecoveryCodeGenerator implements RandomRecoveryCodeGeneratorInterface
{
    // 紛らわしい 0/O/1/I/L を除外した文字集合 (Crockford base32 風)
    private const string ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const int GROUP_LENGTH = 4;

    private const int GROUP_COUNT = 2;

    public function __construct(private Randomizer $randomizer)
    {
    }

    #[Override]
    public function generate(): string
    {
        // Randomizer の既定エンジンは CSPRNG (Random\Engine\Secure)
        // getBytesFromString で文字集合から一様にサンプリングし、4 文字ごとにハイフン区切りにする
        $raw = $this->randomizer->getBytesFromString(self::ALPHABET, self::GROUP_LENGTH * self::GROUP_COUNT);

        return implode('-', str_split($raw, self::GROUP_LENGTH));
    }
}
