<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Get;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Get\GetInputData;
use Release\Application\Admin\UseCase\Group\Get\GetUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function found(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId2, $releaseGroupId, 'CD+DVD', true, new ImmutableDate('2026-06-01'), formats: [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value], media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
                ['position' => 2, 'name' => null, 'tracks' => []],
            ], orderNo: 10),
            $this->createRelease($releaseId1, $releaseGroupId, '配信', true, new ImmutableDate('2026-05-01'), formats: [ReleaseFormat::Digital->value], media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ], orderNo: 20),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseGroupId));

        $this->assertSame($releaseGroupId, $result->releaseGroup->releaseGroupId->value);

        // 傘下リリースは発売日順、formatValues は提供形態の値順
        $releases = $result->releases;
        $this->assertCount(2, $releases);
        $this->assertSame($releaseId1, $releases[0]->releaseId);
        $this->assertSame(20, $releases[0]->orderNo);
        $this->assertSame([ReleaseFormat::Digital->value], $releases[0]->formatValues);
        $this->assertSame($releaseId2, $releases[1]->releaseId);
        $this->assertSame(10, $releases[1]->orderNo);
        $this->assertSame([ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value], $releases[1]->formatValues);
    }

    #[Test]
    public function sortsReleasesByOrderNoWhenReleasedOnIsSame(): void
    {
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '同日リリースの作品', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId2, $releaseGroupId, '同日で order_no が大きい版', true, new ImmutableDate('2026-01-01'), orderNo: 2),
            $this->createRelease($releaseId1, $releaseGroupId, '同日で order_no が小さい版', true, new ImmutableDate('2026-01-01'), orderNo: 1),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseGroupId));

        $this->assertSame($releaseId1, $result->releases[0]->releaseId);
        $this->assertSame($releaseId2, $result->releases[1]->releaseId);
    }

    #[Test]
    public function invalidId(): void
    {
        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->handle(new GetInputData('invalid-id'));
    }

    #[Test]
    public function notFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new GetInputData($this->generateUuid()));
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
