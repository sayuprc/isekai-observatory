<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Viewer\V1\SiteStats;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use SiteStats\Route\ViewerSiteStatsRouteMap;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetSiteStatsTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function showPublicCounts(): void
    {
        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '公開楽曲 1',
                '公開テスト楽曲説明 1',
                SongType::Original,
                true,
                1,
            ),
            $this->createSong(
                $this->generateUuid(),
                '公開楽曲 2',
                '公開テスト楽曲説明 2',
                SongType::Cover,
                true,
                2,
            ),
            $this->createSong(
                $this->generateUuid(),
                '非公開楽曲',
                '非公開テスト楽曲説明',
                SongType::Cover,
                false,
                3,
            ),
        );

        $visibleGroupId = $this->generateUuid();
        $hiddenGroupId = $this->generateUuid();
        $emptyGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($visibleGroupId, '公開グループ', ReleaseGroupType::Album, true),
            $this->createReleaseGroup($hiddenGroupId, '非公開グループ', ReleaseGroupType::Single, false),
            $this->createReleaseGroup($emptyGroupId, '公開リリース無しグループ', ReleaseGroupType::Ep, true),
        );
        $this->storeReleases(
            $this->createRelease($this->generateUuid(), $visibleGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $hiddenGroupId, '通常盤', true),
            $this->createRelease($this->generateUuid(), $emptyGroupId, '非公開盤', false),
        );

        $this->get(route(ViewerSiteStatsRouteMap::Get))
            ->assertStatus(200)
            ->assertExactJson([
                'songCount' => 2,
                'releaseCount' => 1,
            ]);
    }
}
