<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tag\Domain\Models\TagReferences;
use Tests\TestCase;

class TagReferencesTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [
            ['tagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['tagId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $result = TagReferences::fromArray($input);

        $tags = $result;

        $this->assertCount(2, $tags);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $tags[0]->tagId->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $tags[1]->tagId->value);
    }

    #[Test]
    public function fromArrayFailsWithDuplicateTagId(): void
    {
        $input = [
            ['tagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['tagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
        ];

        try {
            TagReferences::fromArray($input);
            $this->fail('BusinessRuleViolationException が発生しませんでした');
        } catch (BusinessRuleViolationException $e) {
            $this->assertSame('同じタグを複数指定することはできません。', $e->getMessage());
        }
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $result = TagReferences::fromArray([]);

        $this->assertCount(0, $result);
    }
}
