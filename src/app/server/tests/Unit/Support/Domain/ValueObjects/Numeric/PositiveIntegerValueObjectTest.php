<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Numeric;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\Numeric\PositiveIntegerValueObject;
use Tests\TestCase;

class PositiveIntegerValueObjectTest extends TestCase
{
    #[Test]
    public function properlyStoresValue(): void
    {
        $this->assertSame(1, new PositiveIntegerObject(1)->value);
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidValue')]
    public function throwExceptionWhenInvalidValue(int $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('正の整数ではありません: ' . $value);

        new PositiveIntegerObject($value);
    }

    public static function provideThrowExceptionWhenInvalidValue(): array
    {
        return [
            [-1],
            [0],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(PositiveIntegerObject $object, PositiveIntegerValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                new PositiveIntegerObject(1),
                new PositiveIntegerObject(1),
                true,
            ],
            [
                new PositiveIntegerObject(1),
                new PositiveIntegerObject(2),
                false,
            ],
            [
                new PositiveIntegerObject(1),
                new OtherPositiveIntegerObject(1),
                false,
            ],
            [
                new PositiveIntegerObject(1),
                new OtherPositiveIntegerObject(2),
                false,
            ],
        ];
    }
}

readonly class PositiveIntegerObject extends PositiveIntegerValueObject
{
}

readonly class OtherPositiveIntegerObject extends PositiveIntegerValueObject
{
}
