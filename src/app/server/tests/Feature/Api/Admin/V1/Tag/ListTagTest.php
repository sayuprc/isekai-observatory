<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Tag;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tag\Route\TagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;

class ListTagTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function showListOrderedByOrderNo(): void
    {
        $converter = $this->app->make(UuidConverterInterface::class);
        $tag1 = '95f4d89a-a6af-4df9-9df9-9ac57a12dc51';
        $tag2 = '2f4ae940-2baa-42ad-ad13-07ed1135e97b';

        DB::table('tags')->insert([
            [
                'tag_id' => $converter->toBin($tag1),
                'name' => '後攻',
                'order_no' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tag_id' => $converter->toBin($tag2),
                'name' => '先攻',
                'order_no' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withAuth()
            ->get(route(TagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    [
                        'tagId' => $tag2,
                        'name' => '先攻',
                        'orderNo' => 10,
                    ],
                    [
                        'tagId' => $tag1,
                        'name' => '後攻',
                        'orderNo' => 20,
                    ],
                ],
            ]);
    }

    #[Test]
    public function requiresAuthentication(): void
    {
        $this->get(route(TagRouteMap::List))->assertStatus(401);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->withAuth()
            ->get(route(TagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [],
            ]);
    }
}
