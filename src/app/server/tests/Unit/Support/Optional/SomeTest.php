<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Optional;

use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Optional\Some;
use Tests\TestCase;

class SomeTest extends TestCase
{
    #[Test]
    public function isPresentReturnsTrue(): void
    {
        $some = new Some('value');

        $this->assertTrue($some->isPresent());
    }

    #[Test]
    public function isEmptyReturnsFalse(): void
    {
        $some = new Some('value');

        $this->assertFalse($some->isEmpty());
    }

    #[Test]
    public function getReturnsValue(): void
    {
        $some = new Some('value');

        $this->assertSame('value', $some->get());
    }

    #[Test]
    public function getReturnsIntValue(): void
    {
        $some = new Some(42);

        $this->assertSame(42, $some->get());
    }

    #[Test]
    public function getReturnsNullValue(): void
    {
        $some = new Some(null);

        $this->assertNull($some->get());
    }

    #[Test]
    public function getReturnsObjectValue(): void
    {
        $object = new stdClass();
        $some = new Some($object);

        $this->assertSame($object, $some->get());
    }
}
