<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tag\Domain\Models\TagChanges;
use Tag\Domain\Models\TagReferences;
use Tests\TestCase;

class TagChangesTest extends TestCase
{
    private const string A = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private const string B = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';

    private const string C = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

    #[Test]
    public function applyAddsAndRemoves(): void
    {
        $changes = TagChanges::fromIds([self::C], [self::A]);

        $result = $changes->apply(TagReferences::fromArray([['tagId' => self::A], ['tagId' => self::B]]));

        $this->assertSame([self::B, self::C], array_column($result->toArray(), 'tag_id'));
    }

    #[Test]
    public function duplicateIdsAreCollapsed(): void
    {
        $changes = TagChanges::fromIds([self::A, self::A], []);

        $this->assertCount(1, $changes->addTagIds);
    }

    #[Test]
    public function isEmptyWhenNothingIsSpecified(): void
    {
        $this->assertTrue(TagChanges::fromIds([], [])->isEmpty());
        $this->assertFalse(TagChanges::fromIds([self::A], [])->isEmpty());
    }

    #[Test]
    public function sameTagInAddAndRemoveIsRejected(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        TagChanges::fromIds([self::A], [self::A]);
    }
}
