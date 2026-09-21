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
     * @return iterable<string, array{MediaType, int, string}>
     */
    public static function provideTypes(): iterable
    {
        yield 'MV' => [MediaType::Mv, 1, 'MV'];
        yield 'AudioVideo' => [MediaType::AudioVideo, 2, '音源動画'];
        yield 'LiveStream' => [MediaType::LiveStream, 3, '配信'];
        yield 'Short' => [MediaType::Short, 4, 'ショート'];
        yield 'Post' => [MediaType::Post, 5, '投稿'];
        yield 'Other' => [MediaType::Other, 99, 'その他'];
    }

    #[Test]
    #[DataProvider('provideTypes')]
    public function valueAndName(MediaType $type, int $value, string $name): void
    {
        $this->assertSame($value, $type->value);
        $this->assertSame($name, $type->getName());
    }

    #[Test]
    public function unknownValueFallsBackToOther(): void
    {
        $this->assertSame(MediaType::Other, MediaType::tryFrom(12345) ?? MediaType::Other);
        $this->assertNull(MediaType::tryFrom(12345));
    }
}
