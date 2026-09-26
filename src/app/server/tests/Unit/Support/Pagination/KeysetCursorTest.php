<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Pagination;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Pagination\KeysetCursor;
use Tests\TestCase;

class KeysetCursorTest extends TestCase
{
    #[Test]
    public function decodesTheKeysItEncoded(): void
    {
        $cursor = KeysetCursor::decode(KeysetCursor::encode(['orderNo' => 3, 'id' => 'abc', 'startOn' => null]));

        $this->assertSame(3, $cursor->int('orderNo'));
        $this->assertSame('abc', $cursor->string('id'));
        $this->assertNull($cursor->nullableString('startOn'));
    }

    #[Test]
    public function encodesAsBase64Json(): void
    {
        // 既に発行済みのカーソルを読めるよう、形式を変えない
        $this->assertSame(base64_encode('{"orderNo":1,"id":"abc"}'), KeysetCursor::encode(['orderNo' => 1, 'id' => 'abc']));
    }

    #[Test]
    #[DataProvider('provideUnreadableValues')]
    public function rejectsUnreadableValues(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        KeysetCursor::decode($value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideUnreadableValues(): array
    {
        return [
            'not base64' => ['***'],
            'not json' => [base64_encode('not json')],
            'not an object' => [base64_encode('"text"')],
        ];
    }

    #[Test]
    public function rejectsMissingOrMistypedKeys(): void
    {
        $cursor = KeysetCursor::decode(KeysetCursor::encode(['orderNo' => '1', 'id' => 1]));

        $this->assertThrows(static fn () => $cursor->int('orderNo'), InvalidArgumentException::class);
        $this->assertThrows(static fn () => $cursor->string('id'), InvalidArgumentException::class);
        $this->assertThrows(static fn () => $cursor->string('missing'), InvalidArgumentException::class);
        $this->assertThrows(static fn () => $cursor->nullableString('missing'), InvalidArgumentException::class);
    }
}
