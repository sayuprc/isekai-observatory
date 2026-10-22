<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Tag;

use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Tag\Route\PublicTagRouteMap;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsOnlyTagsAttachedToPublicRecordsInOrderNo(): void
    {
        $songTagId = $this->generateUuid();
        $eventTagId = $this->generateUuid();
        $releaseGroupTagId = $this->generateUuid();
        $mediaTagId = $this->generateUuid();
        $privateTagId = $this->generateUuid();
        $emptyGroupTagId = $this->generateUuid();
        $unusedTagId = $this->generateUuid();
        $this->storeTags(
            $this->createTag($mediaTagId, 'メディアのタグ', 1),
            $this->createTag($releaseGroupTagId, 'リリースグループのタグ', 2),
            $this->createTag($eventTagId, 'イベントのタグ', 3),
            $this->createTag($songTagId, '楽曲のタグ', 4),
            $this->createTag($privateTagId, '非公開記録のタグ', 5),
            $this->createTag($emptyGroupTagId, '公開リリースの無い作品のタグ', 6),
            $this->createTag($unusedTagId, '未使用のタグ', 7),
        );

        $publicGroupId = $this->generateUuid();
        $emptyGroupId = $this->generateUuid();
        $this->storeSongs(
            $this->createSong($this->generateUuid(), '公開楽曲', '説明', SongType::Original, true, 1, [['tagId' => $songTagId]]),
            $this->createSong($this->generateUuid(), '非公開楽曲', '説明', SongType::Original, false, 2, [['tagId' => $privateTagId]]),
        );
        $this->storeEvents(
            $this->createEvent($this->generateUuid(), tags: [['tagId' => $eventTagId]]),
            $this->createEvent($this->generateUuid(), isDisplay: false, tags: [['tagId' => $privateTagId]]),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($publicGroupId, '公開の作品', ReleaseGroupType::Single, tags: [['tagId' => $releaseGroupTagId]]),
            $this->createReleaseGroup($emptyGroupId, '公開リリースの無い作品', ReleaseGroupType::Single, tags: [['tagId' => $emptyGroupTagId]]),
            $this->createReleaseGroup($this->generateUuid(), '非公開の作品', ReleaseGroupType::Single, isDisplay: false, tags: [['tagId' => $privateTagId]]),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $publicGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $emptyGroupId, '非公開盤', false),
        );
        $this->storeMedia(
            $this->createMedia($this->generateUuid(), '公開 MV', 'https://example.com/public', MediaType::Mv, true, tags: [['tagId' => $mediaTagId]]),
            $this->createMedia($this->generateUuid(), '非公開 MV', 'https://example.com/private', MediaType::Mv, false, tags: [['tagId' => $privateTagId]]),
        );

        $this->get(route(PublicTagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [
                    ['tagId' => $mediaTagId, 'name' => 'メディアのタグ'],
                    ['tagId' => $releaseGroupTagId, 'name' => 'リリースグループのタグ'],
                    ['tagId' => $eventTagId, 'name' => 'イベントのタグ'],
                    ['tagId' => $songTagId, 'name' => '楽曲のタグ'],
                ],
            ]);
    }

    #[Test]
    public function paginatesInOrderNoWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $this->storeTags(
            $this->createTag($secondId, 'タグ B', 2),
            $this->createTag($firstId, 'タグ A', 1),
        );
        $this->storeEvents($this->createEvent($this->generateUuid(), tags: [['tagId' => $secondId], ['tagId' => $firstId]]));

        $first = $this->get(route(PublicTagRouteMap::List, ['pageSize' => 1]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.tagId', $firstId);

        $this->get(route(PublicTagRouteMap::List, ['pageSize' => 1, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.tagId', $secondId)
            ->assertJsonMissingPath('nextPageToken');
    }
}
