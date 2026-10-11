<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Notification\Contracts\Embed;

use PHPUnit\Framework\Attributes\Test;
use Support\Notification\Contracts\Embed\Field;
use Tests\TestCase;

class FieldTest extends TestCase
{
    #[Test]
    public function toArrayIncludesInlineByDefault(): void
    {
        $field = new Field();

        $this->assertSame(
            [
                'inline' => false,
            ],
            $field->toArray(),
        );
    }

    #[Test]
    public function toArrayIncludesPresentNameAndValue(): void
    {
        $field = new Field(name: 'channel_id', value: 'UCxxxx', inline: true);

        $this->assertSame(
            [
                'inline' => true,
                'name' => 'channel_id',
                'value' => 'UCxxxx',
            ],
            $field->toArray(),
        );
    }

    #[Test]
    public function toArrayOmitsBlankStringsAfterTrim(): void
    {
        $field = new Field(name: '  ', value: '  value  ');

        $this->assertSame(
            [
                'inline' => false,
                'value' => 'value',
            ],
            $field->toArray(),
        );
    }
}
