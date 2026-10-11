<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Infrastructures\Uuid;

use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Support\Infrastructures\Uuid\UuidConverter;
use Tests\TestCase;

class UuidConverterTest extends TestCase
{
    #[Test]
    public function toBin(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $bin = $this->getInstance()->toBin($uuid);

        $this->assertSame(16, strlen($bin));
        $this->assertSame($bin, Uuid::fromString($uuid)->getBytes());
    }

    #[Test]
    public function binToUuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $bin = Uuid::fromString($uuid)->getBytes();

        $result = $this->getInstance()->toUuid($bin);

        $this->assertSame($uuid, $result);
    }

    #[Test]
    public function roundTrip(): void
    {
        $converter = $this->getInstance();
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $this->assertSame($uuid, $converter->toUuid($converter->toBin($uuid)));
    }

    private function getInstance(): UuidConverter
    {
        return new UuidConverter();
    }
}
