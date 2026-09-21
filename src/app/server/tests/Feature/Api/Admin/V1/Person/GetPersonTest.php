<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Person;

use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetPersonTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, 'テスト人物', 1));

        $this->withAuth()
            ->get(route(PersonRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'person' => [
                    'personId' => $uuid,
                    'name' => 'テスト人物',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->withAuth()
            ->get(route(PersonRouteMap::Get, $this->generateUuid()))
            ->assertStatus(404);
    }
}
