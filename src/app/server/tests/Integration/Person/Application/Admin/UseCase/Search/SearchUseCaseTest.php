<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Search;

use Person\Application\Admin\UseCase\Search\SearchInputData;
use Person\Application\Admin\UseCase\Search\SearchUseCase;
use Person\Domain\Criteria\Sort;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function search(): void
    {
        $person = $this->createPerson($this->generateUuid(), 'テスト人物', 1);
        $this->storePersons($person, $this->createPerson($this->generateUuid(), '春猿火', 2));

        $result = $this->getInstance()->handle(new SearchInputData(name: 'テスト人物'));

        $this->assertEquals([$person], $result->persons);
        $this->assertSame(1, $result->maxPage);
    }

    #[Test]
    public function searchSorted(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), 'あ', 1);
        $person2 = $this->createPerson($this->generateUuid(), 'い', 2);
        $this->storePersons($person1, $person2);

        $result = $this->getInstance()->handle(new SearchInputData(sort: Sort::Name, order: Order::Desc));

        $this->assertEquals([$person2, $person1], $result->persons);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
