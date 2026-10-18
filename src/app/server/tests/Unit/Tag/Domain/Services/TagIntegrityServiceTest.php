<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tag\Domain\Models\TagName;
use Tag\Domain\Models\TagRepositoryInterface;
use Tag\Domain\Services\TagIntegrityService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class TagIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&TagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->repository = Mockery::mock(TagRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $name = 'テストタグA';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 20;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = $this->createTag($uuid, $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (TagName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertEquals($expectedTag, $result);
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = 'テストタグA';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 20;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = $this->createTag($uuid, $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (TagName $arg): bool => $arg->value === $name)
            ->andReturn($this->createTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, 10))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForCreate($name);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $tagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 20;

        $expectedTag = $this->createTag($tagId, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (TagName $arg): bool => $arg->value === $name)
            ->andReturn($expectedTag)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($tagId, $name, $orderNo);

        $this->assertEquals($expectedTag, $result);
    }

    #[Test]
    public function prepareForUpdateDuplicateName(): void
    {
        $tagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 20;

        $expectedTag = $this->createTag($tagId, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (TagName $arg): bool => $arg->value === $name)
            ->andReturn($this->createTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, 10))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->prepareForUpdate($tagId, $name, $orderNo);
    }

    private function getInstance(): TagIntegrityService
    {
        return new TagIntegrityService(
            $this->generator,
            $this->repository,
        );
    }
}
