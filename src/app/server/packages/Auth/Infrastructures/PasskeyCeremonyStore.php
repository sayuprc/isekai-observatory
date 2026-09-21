<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Services\PasskeyConfig;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Override;

readonly class PasskeyCeremonyStore implements PasskeyCeremonyStoreInterface
{
    public function __construct(
        private CacheFactory $cache,
        private PasskeyConfig $config,
    ) {
    }

    #[Override]
    public function put(PasskeyCeremonyState $state): void
    {
        $store = $this->store();

        $store->put(
            $this->key($state->authCeremonyId),
            $state->toArray(),
            $this->config->ceremonyTtlSeconds,
        );
    }

    #[Override]
    public function pull(string $authCeremonyId): ?PasskeyCeremonyState
    {
        $store = $this->store();

        try {
            /** @var array<string, mixed>|null $payload */
            $payload = $store->withoutOverlapping(
                $this->lockKey($authCeremonyId),
                fn (): mixed => $store->pull($this->key($authCeremonyId)),
                10,
                5,
            );
        } catch (LockTimeoutException) {
            return null;
        }

        if (is_null($payload)) {
            return null;
        }

        /** @var array{
         *   auth_ceremony_id: string,
         *   type: string,
         *   email: string,
         *   name: string|null,
         *   admin_user_id: string,
         *   options_json: string
         * } $payload
         */
        return PasskeyCeremonyState::fromArray($payload);
    }

    private function key(string $authCeremonyId): string
    {
        return 'auth:passkey:ceremony:' . $authCeremonyId;
    }

    private function lockKey(string $authCeremonyId): string
    {
        return $this->key($authCeremonyId) . ':lock';
    }

    private function store(): Repository
    {
        $store = $this->cache->store($this->config->ceremonyCacheStore);
        assert($store instanceof Repository);

        return $store;
    }
}
