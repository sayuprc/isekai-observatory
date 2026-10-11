<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class StringValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new StringObject($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [''],
            ['value'],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(StringObject $object, StringValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                new StringObject('1'),
                new StringObject('1'),
                true,
            ],
            [
                new StringObject('1'),
                new StringObject('2'),
                false,
            ],
            [
                new StringObject('1'),
                new OtherStringObject('1'),
                false,
            ],
            [
                new StringObject('1'),
                new OtherStringObject('2'),
                false,
            ],
        ];
    }
}

readonly class StringObject extends StringValueObject
{
}

readonly class OtherStringObject extends StringValueObject
{
}
