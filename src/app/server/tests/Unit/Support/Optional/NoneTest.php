<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Optional;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Support\Optional\None;
use Tests\TestCase;

class NoneTest extends TestCase
{
    #[Test]
    public function isPresentReturnsFalse(): void
    {
        $none = new None();

        $this->assertFalse($none->isPresent());
    }

    #[Test]
    public function isEmptyReturnsTrue(): void
    {
        $none = new None();

        $this->assertTrue($none->isEmpty());
    }

    #[Test]
    public function getThrowsLogicException(): void
    {
        $none = new None();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('None から値は取得できません。');

        $none->get();
    }
}
