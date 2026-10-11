<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Date;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;
use Tests\TestCase;

class ImmutableDateTimeValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(DateTimeImmutable $value): void
    {
        $this->assertSame($value->format('Y-m-d H:i:s'), new ImmutableDateTime($value)->value->format('Y-m-d H:i:s'));
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [new DateTimeImmutable()],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(ImmutableDateTime $object, ImmutableDateTimeValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        $now = new DateTimeImmutable();

        return [
            [
                new ImmutableDateTime($now),
                new ImmutableDateTime($now),
                true,
            ],
            [
                new ImmutableDateTime($now),
                new ImmutableDateTime($now->modify('+1 seconds')),
                false,
            ],
            [
                new ImmutableDateTime($now),
                new OtherImmutableDateTime($now),
                false,
            ],
            [
                new ImmutableDateTime($now),
                new OtherImmutableDateTime($now->modify('+1 seconds')),
                false,
            ],
        ];
    }
}

readonly class ImmutableDateTime extends ImmutableDateTimeValueObject
{
}

readonly class OtherImmutableDateTime extends ImmutableDateTimeValueObject
{
}
