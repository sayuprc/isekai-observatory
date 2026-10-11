<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Infrastructures\Mapper;
use Tests\TestCase;

class MapperTest extends TestCase
{
    #[Test]
    public function mapFromArray(): void
    {
        $source = [
            'id' => 1,
            'name' => 'sample',
            'items' => [
                [
                    'id' => 1,
                    'name' => 'item name 1',
                ],
                [
                    'id' => 2,
                    'name' => 'item name 2',
                ],
            ],
        ];

        $instance = $this->getMapper()->map(Sample::class, $source);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $source['id']);
        $this->assertSame($instance->name, $source['name']);
        $this->assertCount(count($source['items']), $instance->items);
        $this->assertSame($instance->items[0]->id, $source['items'][0]['id']);
        $this->assertSame($instance->items[0]->name, $source['items'][0]['name']);
        $this->assertSame($instance->items[1]->id, $source['items'][1]['id']);
        $this->assertSame($instance->items[1]->name, $source['items'][1]['name']);
    }

    #[Test]
    public function mapFromStdClass(): void
    {
        $item1 = new stdClass();
        $item1->id = 1;
        $item1->name = 'item name 1';

        $item2 = new stdClass();
        $item2->id = 2;
        $item2->name = 'item name 2';

        $source = new stdClass();
        $source->id = 1;
        $source->name = 'sample';
        $source->items = [$item1, $item2];

        $instance = $this->getMapper()->map(Sample::class, $source);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $source->id);
        $this->assertSame($instance->name, $source->name);
        $this->assertCount(count($source->items), $instance->items);
        $this->assertSame($instance->items[0]->id, $source->items[0]->id);
        $this->assertSame($instance->items[0]->name, $source->items[0]->name);
        $this->assertSame($instance->items[1]->id, $source->items[1]->id);
        $this->assertSame($instance->items[1]->name, $source->items[1]->name);
    }

    #[Test]
    public function mapFromJson(): void
    {
        $source = json_encode([
            'id' => 1,
            'name' => 'sample',
            'items' => [
                [
                    'id' => 1,
                    'name' => 'item name 1',
                ],
                [
                    'id' => 2,
                    'name' => 'item name 2',
                ],
            ],
        ]);

        $instance = $this->getMapper()->map(Sample::class, $source);

        $expected = json_decode($source, true);

        $this->assertInstanceOf(Sample::class, $instance);
        $this->assertSame($instance->id, $expected['id']);
        $this->assertSame($instance->name, $expected['name']);
        $this->assertCount(count($expected['items']), $instance->items);
        $this->assertSame($instance->items[0]->id, $expected['items'][0]['id']);
        $this->assertSame($instance->items[0]->name, $expected['items'][0]['name']);
        $this->assertSame($instance->items[1]->id, $expected['items'][1]['id']);
        $this->assertSame($instance->items[1]->name, $expected['items'][1]['name']);
    }

    private function getMapper(): Mapper
    {
        return $this->app->get(Mapper::class);
    }
}

class Sample
{
    /**
     * @param array<Item> $items
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly array $items,
    ) {
    }
}

class Item
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }
}
