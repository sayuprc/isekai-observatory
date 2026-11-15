<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Release\Domain\Models\ReleaseId;
use Release\Infrastructures\ReleaseRepository;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ReleaseRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function findByIdsReturnsEachReleaseWithItsOwnChildren(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '収録曲', '説明', SongType::Original, true, 1));
        $releaseGroupId = $this->generateUuid();
        $this->storeReleaseGroups($this->createReleaseGroup($releaseGroupId, 'アルバム', ReleaseGroupType::Album));

        // 両方のリリースが同じ position / trackNo を持ち、リリース間で子が混ざらないことを確かめる
        $cd = $this->createRelease(
            $this->generateUuid(),
            $releaseGroupId,
            '通常盤',
            true,
            formats: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songIds' => [$songId], 'title' => null, 'trackNo' => 1],
                        ['songIds' => [], 'title' => 'ボーナストラック', 'trackNo' => 2],
                    ],
                ],
            ],
        );
        $limited = $this->createRelease(
            $this->generateUuid(),
            $releaseGroupId,
            '初回限定盤',
            true,
            formats: [ReleaseFormat::Cd->value, ReleaseFormat::BluRay->value],
            media: [
                [
                    'position' => 1,
                    'name' => 'CD',
                    'tracks' => [
                        ['songIds' => [], 'title' => 'オーバーチュア', 'trackNo' => 1],
                    ],
                ],
                ['position' => 2, 'name' => 'Blu-ray', 'tracks' => []],
            ],
        );
        $this->getInstance()->save($cd);
        $this->getInstance()->save($limited);

        $found = $this->getInstance()->findByIds(
            $cd->releaseId,
            $limited->releaseId,
            new ReleaseId($this->generateUuid()),
        );

        $this->assertEqualsCanonicalizing([$cd, $limited], $found);
    }

    private function getInstance(): ReleaseRepository
    {
        return $this->app->make(ReleaseRepository::class);
    }
}
