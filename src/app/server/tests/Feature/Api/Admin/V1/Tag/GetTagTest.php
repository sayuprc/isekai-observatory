<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Tag;

use PHPUnit\Framework\Attributes\Test;
use Tag\Infrastructures\TagRepository;
use Tag\Route\TagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(TagRepository::class)->save($this->createTag($uuid, 'テストタグA', 1));

        $this->withAuth()
            ->get(route(TagRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'tagId' => $uuid,
                    'name' => 'テストタグA',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(TagRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
