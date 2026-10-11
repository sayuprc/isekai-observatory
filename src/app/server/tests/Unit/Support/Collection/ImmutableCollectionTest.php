<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Collection;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Support\Collection\ImmutableCollection;
use Tests\TestCase;

class ImmutableCollectionTest extends TestCase
{
    #[Test]
    public function canCount(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->assertSame(3, count($collection));
        $this->assertSame(3, $collection->count());
    }

    #[Test]
    public function iterable(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        foreach ($collection as $key => $item) {
            if ($key === 0) {
                $this->assertSame(1, $item);
            } elseif ($key === 1) {
                $this->assertSame(2, $item);
            } elseif ($key === 2) {
                $this->assertSame(3, $item);
            }
        }
    }

    #[Test]
    public function offsetExists(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[1]));
        $this->assertTrue(isset($collection[2]));
        $this->assertFalse(isset($collection[3]));
    }

    #[Test]
    public function offsetGet(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->assertSame(1, $collection[0]);
        $this->assertSame(2, $collection[1]);
        $this->assertSame(3, $collection[2]);
    }

    #[Test]
    public function offsetSetThrowException(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('このコレクションは不変で、要素の変更はできません');

        $collection[0] = 4;
    }

    #[Test]
    public function offsetUnsetThrowException(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('このコレクションは不変で、要素の変更はできません');

        unset($collection[0]);
    }

    #[Test]
    public function toGeneric(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $generic = $collection->toGeneric();

        $this->assertSame([1, 2, 3], $generic->toArray());
    }

    #[Test]
    public function has(): void
    {
        $collection = new IntCollection([1, 2, 3]);

        $this->assertTrue($collection->has(1));
        $this->assertFalse($collection->has(4));
    }
}

/**
 * @extends ImmutableCollection<int, int>
 */
readonly class IntCollection extends ImmutableCollection
{
}
