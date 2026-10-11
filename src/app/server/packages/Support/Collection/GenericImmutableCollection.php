<?php

declare(strict_types=1);

namespace Support\Collection;

use Closure;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ImmutableCollection<TKey, TValue>
 */
final readonly class GenericImmutableCollection extends ImmutableCollection
{
    /**
     * @template TReturn
     *
     * @param Closure(TValue): TReturn $callback
     *
     * @return static<TKey, TReturn>
     */
    public function map(Closure $callback): self
    {
        $newItems = array_map($callback, $this->items);

        return new self($newItems);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return list<TValue>
     */
    public function toList(): array
    {
        return array_values($this->items);
    }
}
