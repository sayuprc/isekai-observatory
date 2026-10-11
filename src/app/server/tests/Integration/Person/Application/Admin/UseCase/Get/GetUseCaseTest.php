<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\Admin\UseCase\Get;

use Person\Application\Admin\UseCase\Get\GetInputData;
use Person\Application\Admin\UseCase\Get\GetUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getting(): void
    {
        $person = $this->createPerson($this->generateUuid(), 'テスト人物', 1);
        $this->storePersons($person);

        $result = $this->getInstance()->handle(new GetInputData($person->personId->value));

        $this->assertEquals($person, $result->person);
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
