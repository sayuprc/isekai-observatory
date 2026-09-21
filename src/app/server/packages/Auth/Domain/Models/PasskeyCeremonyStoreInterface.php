<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

interface PasskeyCeremonyStoreInterface
{
    public function put(PasskeyCeremonyState $state): void;

    public function pull(string $authCeremonyId): ?PasskeyCeremonyState;
}
