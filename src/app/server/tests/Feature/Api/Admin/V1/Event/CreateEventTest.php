<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Route\EventRouteMap;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\Test;
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

        $performanceId = $this->generateUuid();
        $response = $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => 'テストライブ',
                'description' => '説明',
                'typeValue' => 1,
                'schedule' => ['type' => 2, 'startDate' => '2026-10-01', 'endDate' => null, 'startDateTime' => null, 'endDateTime' => null, 'timeZone' => null],
                'statusValue' => null,
                'postponedToEventId' => null,
                'isDisplay' => true,
                'venueIds' => [$venueId],
                'mediaIds' => [$mediaId],
                'sources' => [['displayName' => '公式', 'url' => 'https://example.com/live', 'orderNo' => 1]],
                'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'songTitle' => '披露曲', 'orderNo' => 1, 'isDisplay' => true, 'coVocalists' => [['personId' => $personId, 'name' => '共演者', 'creditName' => 'ゲスト', 'orderNo' => 1]]]],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '本編', 'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'songTitle' => '披露曲', 'orderNo' => 1, 'isDisplay' => true, 'coVocalists' => []]]]],
            ])
            ->assertStatus(200)
            ->assertJsonPath('event.title', 'テストライブ')
            ->assertJsonPath('event.venues.0.venueId', $venueId)
            ->assertJsonPath('event.media.0.mediaId', $mediaId)
            ->assertJsonPath('event.performances.0.songId', $songId)
            ->assertJsonPath('event.performances.0.coVocalists.0.creditName', 'ゲスト');

        $eventId = $response->json('event.eventId');
        $this->assertIsString($eventId);
        $this->assertAuditLogCount(1);
        $this->assertSame('テストライブ', $this->findAuditLog(AuditAction::Create, AuditTargetType::Event, $eventId)['snapshot']['title']);
    }

    #[Test]
    public function rejectsSetlistForExhibition(): void
    {
        $this->withAuth()
            ->postJson(route(EventRouteMap::Create), [
                'title' => '展示',
                'description' => null,
                'typeValue' => 3,
                'schedule' => ['type' => 2, 'startDate' => '2026-10-01', 'endDate' => null, 'startDateTime' => null, 'endDateTime' => null, 'timeZone' => null],
                'statusValue' => null,
                'postponedToEventId' => null,
                'isDisplay' => true,
                'venueIds' => [],
                'mediaIds' => [],
                'sources' => [],
                'performances' => [],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '展示作品', 'performances' => []]],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
