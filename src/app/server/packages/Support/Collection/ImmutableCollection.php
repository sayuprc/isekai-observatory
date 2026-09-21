<?php

declare(strict_types=1);

namespace Support\Collection;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use Override;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements IteratorAggregate<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
abstract readonly class ImmutableCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(protected array $items)
    {
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param TKey $offset
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param TKey|null $offset
     * @param TValue    $value
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('このコレクションは不変で、要素の変更はできません');
    }

    /**
     * @param TKey $offset
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('このコレクションは不変で、要素の変更はできません');
    }

    /**
     * @return GenericImmutableCollection<TKey, TValue>
     */
    public function toGeneric(): GenericImmutableCollection
    {
        return new GenericImmutableCollection($this->items);
    }

    /**
     * @param TValue $value
     */
    public function has(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }
}
