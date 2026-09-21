<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Tests\TestCase;

class ImmutableDateValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideTimeIsZero')]
    public function timeIsZero(ImmutableDate $value): void
    {
        $this->assertSame($value->format('Y-m-d 00:00:00.000000'), new Date($value)->value->format('Y-m-d H:i:s.u'));
    }

    public static function provideTimeIsZero(): array
    {
        return [
            [new ImmutableDate()],
            [new ImmutableDate('2019-12-09 10:28:31')],
            [new ImmutableDate('2019-12-09 10:28:31.000001')],
            [new ImmutableDate('2019-12-09 10:28:31.282930')],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(ImmutableDateValueObject $object, ImmutableDateValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        $now = new ImmutableDate();

        return [
            [
                new Date($now),
                new Date($now),
                true,
            ],
            [
                new Date($now),
                new Date($now->modify('+1 days')),
                false,
            ],
            [
                new Date($now),
                new OtherDate($now),
                false,
            ],
            [
                new Date($now),
                new OtherDate($now->modify('+1 days')),
                false,
            ],
        ];
    }
}

readonly class Date extends ImmutableDateValueObject
{
}

readonly class OtherDate extends ImmutableDateValueObject
{
}
