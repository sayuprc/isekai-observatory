<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\List;

use Person\Application\Admin\UseCase\List\ListUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function list(): void
    {
        $person = $this->createPerson($this->generateUuid(), 'テスト人物', 1);
        $this->storePersons($person);

        $result = $this->getInstance()->handle();

        $this->assertEquals([$person], $result->persons);
    }

    private function getInstance(): ListUseCase
    {
        $this->privilegedContext();

        return $this->app->make(ListUseCase::class);
    }
}
