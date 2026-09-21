<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Numeric;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Numeric\IntegerValueObject;
use Tests\TestCase;

class IntegerValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(int $value): void
    {
        $this->assertSame($value, new IntegerObject($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(IntegerObject $object, IntegerValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                new IntegerObject(1),
                new IntegerObject(1),
                true,
            ],
            [
                new IntegerObject(1),
                new IntegerObject(2),
                false,
            ],
            [
                new IntegerObject(1),
                new OtherIntegerObject(1),
                false,
            ],
            [
                new IntegerObject(1),
                new OtherIntegerObject(2),
                false,
            ],
        ];
    }
}

readonly class IntegerObject extends IntegerValueObject
{
}

readonly class OtherIntegerObject extends IntegerValueObject
{
}
