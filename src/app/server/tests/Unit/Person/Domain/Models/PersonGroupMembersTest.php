<?php

declare(strict_types=1);

namespace Tests\Unit\Person\Domain\Models;

use Person\Domain\Models\PersonGroupMembers;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\TestCase;

class PersonGroupMembersTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $members = PersonGroupMembers::fromArray([
            ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'orderNo' => 1],
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 2],
        ]);

        $this->assertCount(2, $members);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $members[0]->personId->value);
        $this->assertSame(1, $members[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $members[1]->personId->value);
        $this->assertSame(2, $members[1]->orderNo->value);
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $this->assertCount(0, PersonGroupMembers::fromArray([]));
    }

    #[Test]
    public function fromArrayFailsWhenPersonIsDuplicated(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('人物グループのメンバーを重複して登録できません');

        PersonGroupMembers::fromArray([
            ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'orderNo' => 1],
            ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'orderNo' => 2],
        ]);
    }

    #[Test]
    public function fromArrayFailsWhenOrderNoIsDuplicated(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('人物グループのメンバー順序を重複して登録できません');

        PersonGroupMembers::fromArray([
            ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'orderNo' => 1],
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1],
        ]);
    }
}
