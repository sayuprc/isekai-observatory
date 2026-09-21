<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongType;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\SongTypeRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;

class ListSongTypeTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $this->withAuth()
            ->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'types' => collect(SongType::cases())
                    ->map(static fn (SongType $type): array => [
                        'name' => $type->getName(),
                        'value' => $type->value,
                    ])
                    ->all(),
            ]);
    }
}
