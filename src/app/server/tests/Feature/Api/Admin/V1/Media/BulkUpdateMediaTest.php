<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Media;

use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaType;
use Media\Infrastructures\MediaRepository;
use Media\Route\MediaRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class BulkUpdateMediaTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canAddTagsToMultipleMedia(): void
    {
        $tagId = $this->generateUuid();
        $otherTagId = $this->generateUuid();
        $first = $this->generateUuid();
        $second = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10), $this->createTag($otherTagId, 'タグB', 20));
        $this->storeMedia(
            $this->createMedia($first, 'MV1', 'https://example.com/1', MediaType::Mv, true),
            $this->createMedia($second, 'MV2', 'https://example.com/2', MediaType::Mv, true, tags: [[
                'tagId' => $tagId,
            ]]),
        );

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$first, $second],
                'addTagIds' => [$tagId, $otherTagId],
            ])
            ->assertOk()
            ->assertExactJson(['updatedCount' => 2]);

        $this->assertSame([$tagId, $otherTagId], $this->tagIdsOf($first));
        $this->assertSame([$tagId, $otherTagId], $this->tagIdsOf($second));

        $this->assertAuditLogCount(2);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Media, $first);
        $this->findAuditLog(AuditAction::Update, AuditTargetType::Media, $second);
    }

    #[Test]
    public function canRemoveTags(): void
    {
        $tagId = $this->generateUuid();
        $keptTagId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10), $this->createTag($keptTagId, 'タグB', 20));
        $this->storeMedia($this->createMedia($mediaId, 'MV', 'https://example.com/mv', MediaType::Mv, true, tags: [
            ['tagId' => $tagId],
            ['tagId' => $keptTagId],
        ]));

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$mediaId],
                'removeTagIds' => [$tagId],
            ])
            ->assertOk();

        $this->assertSame([$keptTagId], $this->tagIdsOf($mediaId));
    }

    #[Test]
    public function canSetDisplay(): void
    {
        $first = $this->generateUuid();
        $second = $this->generateUuid();
        $this->storeMedia(
            $this->createMedia($first, 'MV1', 'https://example.com/1', MediaType::Mv, true),
            $this->createMedia($second, 'MV2', 'https://example.com/2', MediaType::Mv, true),
        );

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$first, $second],
                'isDisplay' => false,
            ])
            ->assertOk()
            ->assertExactJson(['updatedCount' => 2]);

        $repository = $this->app->make(MediaRepository::class);
        $this->assertFalse($repository->find(new MediaId($first))?->isDisplay);
        $this->assertFalse($repository->find(new MediaId($second))?->isDisplay);
    }

    #[Test]
    public function nothingChangesWhenAnyMediaDoesNotExist(): void
    {
        $tagId = $this->generateUuid();
        $existing = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10));
        $this->storeMedia($this->createMedia($existing, 'MV', 'https://example.com/mv', MediaType::Mv, true));

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$existing, $this->generateUuid()],
                'addTagIds' => [$tagId],
                'isDisplay' => false,
            ])
            ->assertNotFound();

        $media = $this->app->make(MediaRepository::class)->find(new MediaId($existing));
        $this->assertTrue($media?->isDisplay);
        $this->assertSame([], $this->tagIdsOf($existing));
    }

    #[Test]
    public function failsWhenTagDoesNotExist(): void
    {
        $mediaId = $this->generateUuid();
        $this->storeMedia($this->createMedia($mediaId, 'MV', 'https://example.com/mv', MediaType::Mv, true));

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$mediaId],
                'addTagIds' => [$this->generateUuid()],
            ])
            ->assertBadRequest()
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function failsWhenNothingIsSpecified(): void
    {
        $mediaId = $this->generateUuid();
        $this->storeMedia($this->createMedia($mediaId, 'MV', 'https://example.com/mv', MediaType::Mv, true));

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), ['mediaIds' => [$mediaId]])
            ->assertBadRequest()
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function failsWhenSameTagIsAddedAndRemoved(): void
    {
        $tagId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $this->storeTags($this->createTag($tagId, 'タグA', 10));
        $this->storeMedia($this->createMedia($mediaId, 'MV', 'https://example.com/mv', MediaType::Mv, true));

        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), [
                'mediaIds' => [$mediaId],
                'addTagIds' => [$tagId],
                'removeTagIds' => [$tagId],
            ])
            ->assertBadRequest()
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function emptyMediaIdsIsRejected(): void
    {
        $this
            ->withAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), ['mediaIds' => [], 'isDisplay' => false])
            ->assertUnprocessable();
    }

    #[Test]
    public function generalUserCannotBulkUpdate(): void
    {
        $mediaId = $this->generateUuid();
        $this->storeMedia($this->createMedia($mediaId, 'MV', 'https://example.com/mv', MediaType::Mv, true));

        $this
            ->withGeneralAuth()
            ->postJson(route(MediaRouteMap::BulkUpdate), ['mediaIds' => [$mediaId], 'isDisplay' => false])
            ->assertForbidden();
    }

    /**
     * @return list<string>
     */
    private function tagIdsOf(string $mediaId): array
    {
        $media = $this->app->make(MediaRepository::class)->find(new MediaId($mediaId));

        return array_column($media?->tags->toArray() ?? [], 'tag_id');
    }
}
