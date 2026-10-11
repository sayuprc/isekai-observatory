<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

class PasskeyConfig
{
    private const int DEFAULT_TIMEOUT_MS = 60000;

    public function __construct(
        public readonly string $rpName,
        public readonly string $rpId,
        public readonly string $origin,
        private readonly int $timeoutMs,
        public readonly int $ceremonyTtlSeconds,
        public readonly string $ceremonyCacheStore,
    ) {
    }

    /**
     * 不正値 (0 以下) の場合は既定値にフォールバックする
     *
     * @return int<1, max>
     */
    public function timeoutMs(): int
    {
        return $this->timeoutMs > 0 ? $this->timeoutMs : self::DEFAULT_TIMEOUT_MS;
    }
}
