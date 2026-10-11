<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Persons;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Persons\SongPersons;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\TestCase;

class SongPersonsTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $result = SongPersons::fromArray([
            ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'role' => 1, 'orderNo' => 1],
            ['personId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'role' => 2, 'orderNo' => 2],
            ['personId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'role' => 3, 'orderNo' => 3],
        ]);

        $persons = $result;

        $this->assertCount(3, $persons);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $persons[0]->personId->value);
        $this->assertSame(1, $persons[0]->role->value);
        $this->assertSame(1, $persons[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $persons[1]->personId->value);
        $this->assertSame(2, $persons[1]->role->value);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $persons[2]->personId->value);
        $this->assertSame(3, $persons[2]->role->value);
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $result = SongPersons::fromArray([]);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function fromArrayFailsWhenSameRoleIsDuplicatedForSamePerson(): void
    {
        try {
            SongPersons::fromArray([
                ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'role' => 1, 'orderNo' => 1],
                ['personId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'role' => 1, 'orderNo' => 2],
            ]);
            $this->fail('BusinessRuleViolationException が発生しませんでした');
        } catch (BusinessRuleViolationException $e) {
            $this->assertSame('同じ人物に同じ role を重複指定できません', $e->getMessage());
        }
    }
}
