<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Infrastructures;

use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Criteria\Sort;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class PersonRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $person1 = $this->createPerson($this->generateUuid(), 'テスト人物1', 1);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト人物2', 2);

        $repository->save($person1);
        $repository->save($person2);

        $this->assertEquals([$person1, $person2], $repository->all());
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $person = $this->createPerson($this->generateUuid(), 'テスト人物1', 1);
        $repository->save($person);

        $found = $repository->find($person->personId);

        $this->assertNotNull($found);
        $this->assertEquals($person, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(new PersonId($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $person = $this->createPerson($this->generateUuid(), 'テスト人物1', 1);

        $repository->save($person);

        $found = $repository->findByName($person->name);

        $this->assertNotNull($found);
        $this->assertEquals($person, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $person = $this->createPerson($this->generateUuid(), 'テスト人物1', 1);

        $repository->save($person);
        $repository->delete($person->personId);

        $this->assertNull($repository->find($person->personId));
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $repository = $this->getInstance();

        $person1 = $this->createPerson($this->generateUuid(), 'テスト人物1', 10);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト人物2', 20);

        $repository->save($person1);
        $repository->save($person2);

        $persons = $repository->search(new PersonSearchCriteria(new None()));

        $this->assertCount(2, $persons);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $person1 = $this->createPerson($this->generateUuid(), 'テスト人物1', 10);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト人物2', 20);

        $repository->save($person1);
        $repository->save($person2);

        $persons = $repository->search(new PersonSearchCriteria(new Some('テスト人物1')));

        $this->assertCount(1, $persons);
        $this->assertEquals($person1, $persons[0]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $person1 = $this->createPerson($this->generateUuid(), 'あ', 10);
        $person2 = $this->createPerson($this->generateUuid(), 'い', 20);

        $repository->save($person1);
        $repository->save($person2);

        $persons = $repository->search(new PersonSearchCriteria(new None(), Sort::Name, Order::Desc));

        $this->assertCount(2, $persons);
        $this->assertEquals($person2, $persons[0]);
        $this->assertEquals($person1, $persons[1]);
    }

    #[Test]
    public function maxPage(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createPerson($this->generateUuid(), 'テスト人物1', 10));

        $this->assertSame(1, $repository->maxPage(new PersonSearchCriteria(new None())));
    }

    #[Test]
    public function findByIds(): void
    {
        $repository = $this->getInstance();

        $person1 = $this->createPerson($this->generateUuid(), 'テスト人物1', 1);
        $person2 = $this->createPerson($this->generateUuid(), 'テスト人物2', 2);
        $person3 = $this->createPerson($this->generateUuid(), 'テスト人物3', 3);

        $repository->save($person1);
        $repository->save($person2);
        $repository->save($person3);

        $found = $repository->findByIds($person1->personId, $person3->personId);

        $this->assertCount(2, $found);
        $this->assertEqualsCanonicalizing(
            [$person1->personId->value, $person3->personId->value],
            array_map(static fn (Person $person): string => $person->personId->value, $found),
        );
    }

    #[Test]
    public function findByIdsReturnsEmptyWhenNoIdsGiven(): void
    {
        $this->assertSame([], $this->getInstance()->findByIds());
    }

    #[Test]
    public function saveUpdatesExistingPerson(): void
    {
        $repository = $this->getInstance();

        $id = $this->generateUuid();
        $repository->save($this->createPerson($id, '旧名', 1));

        $updated = $this->createPerson($id, '新名', 5);
        $repository->save($updated);

        $found = $repository->find($updated->personId);

        $this->assertNotNull($found);
        $this->assertEquals($updated, $found);
        $this->assertCount(1, $repository->all());
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createPerson($this->generateUuid(), 'テスト人物1', 3));
        $repository->save($this->createPerson($this->generateUuid(), 'テスト人物2', 7));

        $this->assertSame(7, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): PersonRepository
    {
        return $this->app->make(PersonRepository::class);
    }
}
