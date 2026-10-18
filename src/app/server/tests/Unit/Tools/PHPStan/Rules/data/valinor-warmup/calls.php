<?php

declare(strict_types=1);

namespace Tests\Unit\Tools\PHPStan\Rules\Data\ValinorWarmup;

use Support\Contracts\MapperInterface;

final class Registered
{
}

final class Unregistered
{
}

final class Calls
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly Other $other,
    ) {
    }

    /**
     * @param array<string, mixed> $source
     */
    public function run(array $source, string $signature): void
    {
        $this->mapper->map(Registered::class, $source);
        $this->mapper->map(Unregistered::class, $source);
        $this->mapper->map(
            Unregistered::class,
            $source,
        );
        $this->mapper->map($signature, $source);
        $this->other->map(Unregistered::class, $source);
    }
}

final class Other
{
    /**
     * @param array<string, mixed> $source
     */
    public function map(string $signature, array $source): void
    {
    }
}
