<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Domain\Models;

use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MediaTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{MediaType, int}>
     */
    public static function provideTypes(): iterable
    {
        yield 'MV' => [MediaType::Mv, 1];
        yield 'AudioVideo' => [MediaType::AudioVideo, 2];
        yield 'LiveStream' => [MediaType::LiveStream, 3];
        yield 'Short' => [MediaType::Short, 4];
        yield 'Post' => [MediaType::Post, 5];
        yield 'Other' => [MediaType::Other, 99];
    }

    #[Test]
    #[DataProvider('provideTypes')]
    public function value(MediaType $type, int $value): void
    {
        $this->assertSame($value, $type->value);
    }

    #[Test]
    public function unknownValueFallsBackToOther(): void
    {
        $this->assertSame(MediaType::Other, MediaType::tryFrom(12345) ?? MediaType::Other);
        $this->assertNull(MediaType::tryFrom(12345));
    }
}
