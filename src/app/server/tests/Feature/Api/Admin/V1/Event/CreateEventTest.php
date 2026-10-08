<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Route\EventRouteMap;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;

class CreateEventTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canCreateEventWithPerformanceSetlistAndRelations(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $venueId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia($this->createMedia($mediaId, '配信アーカイブ', 'https://example.com/archive', MediaType::LiveStream, true));
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();
        $this->storeReleaseGroups($this->createReleaseGroup($releaseGroupId, 'ライブ映像作品', ReleaseGroupType::Other));
        $this->storeReleases($this->createRelease($releaseId, $releaseGroupId, 'Blu-ray', true, formats: [ReleaseFormat::BluRay->value]));

        $performanceId = $this->generateUuid();
        $response = $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => 'テストライブ',
                'description' => '説明',
                'type' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => 1,
                'isDisplay' => true,
                'venues' => [['venueId' => $venueId, 'orderNo' => 1]],
                'media' => [['mediaId' => $mediaId, 'orderNo' => 1]],
                'releases' => [['releaseId' => $releaseId, 'orderNo' => 1]],
                'sources' => [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
                'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'creditName' => 'ゲスト', 'personGroupId' => null, 'orderNo' => 1]]]],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '本編', 'performanceIds' => [$performanceId]]],
            ])
            ->assertStatus(200)
            ->assertJsonPath('event.title', 'テストライブ')
            ->assertJsonPath('event.status', 1)
            ->assertJsonPath('event.venues.0.venueId', $venueId)
            ->assertJsonPath('event.venues.0.name', '会場')
            ->assertJsonPath('event.media.0.mediaId', $mediaId)
            ->assertJsonPath('event.media.0.title', '配信アーカイブ')
            ->assertJsonPath('event.releases.0.releaseId', $releaseId)
            ->assertJsonPath('event.releases.0.releaseGroupId', $releaseGroupId)
            ->assertJsonPath('event.releases.0.releaseGroupTitle', 'ライブ映像作品')
            ->assertJsonPath('event.releases.0.name', 'Blu-ray')
            ->assertJsonPath('event.releases.0.isDisplay', true)
            ->assertJsonPath('event.releases.0.formats', [ReleaseFormat::BluRay->value])
            ->assertJsonPath('event.performances.0.songId', $songId)
            ->assertJsonPath('event.performances.0.songTitle', '披露曲')
            ->assertJsonPath('event.performances.0.coVocalists.0.name', '共演者')
            ->assertJsonPath('event.performances.0.coVocalists.0.creditName', 'ゲスト')
            ->assertJsonPath('event.performances.0.coVocalists.0.personGroup', null)
            ->assertJsonPath('event.setlist.0.performances.0.songTitle', '披露曲');

        $eventId = $response->json('event.eventId');
        $this->assertIsString($eventId);
        $this->assertAuditLogCount(1);
        $this->assertSame('テストライブ', $this->findAuditLog(AuditAction::Create, AuditTargetType::Event, $eventId)['snapshot']['title']);
    }

    #[Test]
    public function canCreateEventWithCoVocalistsAsPersonGroup(): void
    {
        $songId = $this->generateUuid();
        $person1 = $this->generateUuid();
        $person2 = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($person1, 'メンバー1', 1), $this->createPerson($person2, 'メンバー2', 2));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$person1, $person2]));

        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => 'テストライブ',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => 1,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                    ['personId' => $person1, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 1],
                    ['personId' => $person2, 'creditName' => null, 'personGroupId' => $personGroupId, 'orderNo' => 2],
                ]]],
                'setlist' => [],
            ])
            ->assertStatus(200)
            ->assertJsonPath('event.performances.0.coVocalists.0.name', 'メンバー1')
            ->assertJsonPath('event.performances.0.coVocalists.0.personGroup.personGroupId', $personGroupId)
            ->assertJsonPath('event.performances.0.coVocalists.0.personGroup.name', 'グループ')
            ->assertJsonPath('event.performances.0.coVocalists.1.personGroup.name', 'グループ');
    }

    #[Test]
    public function rejectsSetlistForExhibition(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '展示',
                'description' => '',
                'type' => 3,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => 1,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '展示作品', 'performanceIds' => []]],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsPerformancesForCancelledEvent(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));

        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '中止されたライブ',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => 3,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
                'setlist' => [],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsSetlistForPostponedEvent(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '延期されたライブ',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => 2,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => 'オープニング', 'performanceIds' => []]],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsNullStatus(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '状態なしのイベント',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'status' => null,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [],
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function canCreateEventWithUndatedSchedule(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '日付未定のイベント',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => null, 'endOn' => null],
                'status' => 1,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [],
            ])
            ->assertStatus(200)
            ->assertJsonPath('event.schedule.startOn', null)
            ->assertJsonPath('event.schedule.endOn', null);
    }

    #[Test]
    public function rejectsEndDateWithoutStartDate(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '終了日だけのイベント',
                'description' => '',
                'type' => 1,
                'schedule' => ['startOn' => null, 'endOn' => '2026-10-03'],
                'status' => 1,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'releases' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
