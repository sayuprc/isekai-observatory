<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class UuidValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new Uuid($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            ['dd23940f-6c8c-4316-a3dd-4ab030fcfcac'],
            ['B47477D8-B090-4163-9A2B-C179CC8E692F'],
        ];
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidFormat')]
    public function throwExceptionWhenInvalidFormat(string $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('形式が不正です: ' . $value);

        new Uuid($value);
    }

    public static function provideThrowExceptionWhenInvalidFormat(): array
    {
        return [
            [''],
            ['invalid format'],
            ['GAAAAAAi-AAbA-AsAA-AAAA-AAAAAAcAAeAA'],
            ['gAAAAlAA-AAAn-AtAA-aAAh-AAAAAtAAAAAp'],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(Uuid $object, UuidValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                new Uuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                new Uuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                true,
            ],
            [
                new Uuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                new Uuid('B47477D8-B090-4163-9A2B-C179CC8E692F'),
                false,
            ],
            [
                new Uuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                new OtherUuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                false,
            ],
            [
                new Uuid('dd23940f-6c8c-4316-a3dd-4ab030fcfcac'),
                new OtherUuid('B47477D8-B090-4163-9A2B-C179CC8E692F'),
                false,
            ],
        ];
    }
}

readonly class Uuid extends UuidValueObject
{
}

readonly class OtherUuid extends UuidValueObject
{
}
