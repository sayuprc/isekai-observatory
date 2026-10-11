<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Notification\Contracts;

use PHPUnit\Framework\Attributes\Test;
use Support\Notification\Contracts\Color;
use Tests\TestCase;

class ColorTest extends TestCase
{
    #[Test]
    public function valuesMatchExpectedPalette(): void
    {
        $this->assertSame(0x34_98DB, Color::Info->value);
        $this->assertSame(0x57_F287, Color::Success->value);
        $this->assertSame(0xFE_E75C, Color::Warning->value);
        $this->assertSame(0xED_4245, Color::Error->value);
        $this->assertSame(0x95_A5A6, Color::Default->value);
    }
}
