<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Domain\Services;

use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use Media\Domain\Services\MediaIntegrityService;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class MediaIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MediaRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->repository = Mockery::mock(MediaRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreateFailsWhenUrlIsDuplicated(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $url = 'https://example.com/media';

        $this->generator->shouldReceive('generate')
            ->once()
            ->andReturn($uuid);

        $this->repository->shouldReceive('findByUrl')
            ->once()
            ->withArgs(static fn (MediaUrl $mediaUrl): bool => $mediaUrl->value === $url)
            ->andReturn(
                $this->createMedia(
                    'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                    '既存メディア',
                    $url,
                    MediaType::Mv,
                    true,
                ),
            );

        try {
            $this->getInstance()->prepareForCreate(
                '新規メディア',
                $url,
                '2024-03-01T12:34:56+09:00',
                MediaType::Mv->value,
                true,
            );
            $this->fail('BusinessRuleViolationException が発生しませんでした');
        } catch (BusinessRuleViolationException $e) {
            $this->assertSame('同じURLのメディアが既に存在します', $e->getMessage());
        }
    }

    #[Test]
    public function prepareForUpdateAllowsSameUrlForSameMedia(): void
    {
        $mediaId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $url = 'https://example.com/media';

        $media = $this->createMedia(
            $mediaId,
            '既存メディア',
            $url,
            MediaType::Mv,
            true,
        );

        $this->repository->shouldReceive('findByUrl')
            ->once()
            ->withArgs(static fn (MediaUrl $mediaUrl): bool => $mediaUrl->value === $url)
            ->andReturn($media);

        $result = $this->getInstance()->prepareForUpdate(
            $mediaId,
            '更新後タイトル',
            $url,
            '2024-04-02T10:20:30+09:00',
            MediaType::Mv->value,
            false,
        );

        $this->assertSame($mediaId, $result->mediaId->value);
        $this->assertSame('2024-04-02 10:20:30', $result->publishedAt?->value->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function prepareForCreateFallsBackToOtherForUnknownType(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->generator->shouldReceive('generate')
            ->once()
            ->andReturn($uuid);

        $this->repository->shouldReceive('findByUrl')
            ->once()
            ->andReturn(null);

        $result = $this->getInstance()->prepareForCreate(
            '新規メディア',
            'https://example.com/media',
            '2024-03-01T12:34:56+09:00',
            12345,
            true,
        );

        $this->assertSame(MediaType::Other, $result->type);
    }

    private function getInstance(): MediaIntegrityService
    {
        return new MediaIntegrityService($this->generator, $this->repository);
    }
}
