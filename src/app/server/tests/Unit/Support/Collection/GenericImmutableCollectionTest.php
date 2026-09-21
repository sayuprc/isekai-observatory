<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Collection;

use PHPUnit\Framework\Attributes\Test;
use Support\Collection\GenericImmutableCollection;
use Tests\TestCase;

class GenericImmutableCollectionTest extends TestCase
{
    #[Test]
    public function map(): void
    {
        $collection = new GenericImmutableCollection([1, 2, 3]);

        $result = $collection->map(static fn (int $i): int => $i + 2);

        $this->assertEquals(new GenericImmutableCollection([3, 4, 5]), $result);
    }

    #[Test]
    public function toArray(): void
    {
        $collection = new GenericImmutableCollection([1, 2, 3]);

        $this->assertSame([1, 2, 3], $collection->toArray());
    }

    #[Test]
    public function toList(): void
    {
        $collection = new GenericImmutableCollection(['a' => 1, 'b' => 2, 'c' => 3]);

        $list = $collection->toList();

        $this->assertTrue(array_is_list($list));
        $this->assertSame([1, 2, 3], $collection->toList());
    }
}
