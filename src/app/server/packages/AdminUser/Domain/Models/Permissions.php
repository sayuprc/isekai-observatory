<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\InvalidDomainException;

/**
 * @extends ImmutableCollection<int, Permission>
 */
readonly class Permissions extends ImmutableCollection
{
    /**
     * @param list<string> $items
     *
     * @throws InvalidDomainException
     */
    public static function fromArray(array $items): self
    {
        $permissions = [];

        foreach ($items as $item) {
            $permissions[] = Permission::tryFrom($item) ?? throw new InvalidDomainException('不正な権限です');
        }

        return new self($permissions);
    }

    /**
     * @param list<string> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(static fn (string $item): Permission => Permission::from($item), $items));
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(static fn (Permission $item): string => $item->value)
            ->toList();
    }
}
