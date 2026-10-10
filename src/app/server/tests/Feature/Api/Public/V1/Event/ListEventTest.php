<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Public\V1\Event;

use Event\Domain\Models\EventType;
use Event\Route\PublicEventRouteMap;
use Media\Domain\Models\MediaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;

class ListEventTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsRelationsAsIds(): void
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $personGroupId = $this->generateUuid();
        $venueId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $performanceId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, '公開楽曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storePersonGroups($this->createPersonGroup($personGroupId, 'グループ', [$personId]));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia($this->createMedia($mediaId, '公開メディア', 'https://example.com/public', MediaType::Mv, true));
        $this->storeEvents($this->createEvent(
            $eventId,
            title: '公開ライブ',
            startOn: '2026-10-01',
            endOn: '2026-10-03',
            venues: [['venueId' => $venueId, 'orderNo' => 1]],
            media: [['mediaId' => $mediaId, 'orderNo' => 1]],
            sources: [['displayName' => '公式', 'url' => 'https://example.com/event', 'orderNo' => 1]],
            performances: [
                ['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [
                    ['personId' => $personId, 'creditName' => '当日のユニット', 'personGroupId' => $personGroupId, 'orderNo' => 1],
                ]],
            ],
            setlist: [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => '本編', 'performanceIds' => [$performanceId]]],
        ));

        $this->get(route(PublicEventRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'items' => [[
                    'eventId' => $eventId,
                    'title' => '公開ライブ',
                    'description' => '',
                    'type' => EventType::Live->value,
                    'schedule' => ['startOn' => '2026-10-01', 'endOn' => '2026-10-03'],
                    'status' => 1,
                    'venueIds' => [$venueId],
                    'mediaIds' => [$mediaId],
                    'releaseIds' => [],
                    'sources' => [['displayName' => '公式', 'url' => 'https://example.com/event']],
                    'performances' => [[
                        'performanceId' => $performanceId,
                        'songId' => $songId,
                        'songTitle' => '公開楽曲',
                        'coVocalists' => [['personId' => $personId, 'creditName' => '当日のユニット', 'personGroupId' => $personGroupId]],
                    ]],
                    'setlist' => [['label' => '本編', 'performanceIds' => [$performanceId]]],
                ]],
            ]);
    }

    #[Test]
    public function hidesIdOfPrivateSongAndKeepsItsTitle(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '非公開楽曲', '説明', SongType::Original, false, 1));
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            performances: [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
        ));

        $this->get(route(PublicEventRouteMap::List))
            ->assertStatus(200)
            ->assertJsonPath('items.0.performances.0.songId', null)
            ->assertJsonPath('items.0.performances.0.songTitle', '非公開楽曲');
    }

    #[Test]
    public function excludesIdsOfPrivateMediaAndReleases(): void
    {
        $publicGroupId = $this->generateUuid();
        $privateGroupId = $this->generateUuid();
        $visibleReleaseId = $this->generateUuid();
        $privateReleaseId = $this->generateUuid();
        $privateGroupReleaseId = $this->generateUuid();
        $visibleMediaId = $this->generateUuid();
        $hiddenMediaId = $this->generateUuid();

        $this->storeMedia(
            $this->createMedia($visibleMediaId, '公開メディア', 'https://example.com/public', MediaType::Mv, true),
            $this->createMedia($hiddenMediaId, '非公開メディア', 'https://example.com/private', MediaType::Mv, false),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($publicGroupId, 'ライブ映像作品', ReleaseGroupType::Other),
            $this->createReleaseGroup($privateGroupId, '非公開の作品', ReleaseGroupType::Other, isDisplay: false),
        );
        $this->storeReleases(
            $this->createRelease($visibleReleaseId, $publicGroupId, 'Blu-ray', true),
            $this->createRelease($privateReleaseId, $publicGroupId, '非公開の版', false, orderNo: 2),
            $this->createRelease($privateGroupReleaseId, $privateGroupId, '非公開グループの版', true),
        );
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            media: [['mediaId' => $hiddenMediaId, 'orderNo' => 1], ['mediaId' => $visibleMediaId, 'orderNo' => 2]],
            releases: [
                ['releaseId' => $privateReleaseId, 'orderNo' => 1],
                ['releaseId' => $visibleReleaseId, 'orderNo' => 2],
                ['releaseId' => $privateGroupReleaseId, 'orderNo' => 3],
            ],
        ));

        $this->get(route(PublicEventRouteMap::List))
            ->assertStatus(200)
            ->assertJsonPath('items.0.mediaIds', [$visibleMediaId])
            ->assertJsonPath('items.0.releaseIds', [$visibleReleaseId]);
    }

    #[Test]
    public function excludesPrivateEvent(): void
    {
        $this->storeEvents($this->createEvent($this->generateUuid(), isDisplay: false));

        $this->get(route(PublicEventRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['items' => []]);
    }

    #[Test]
    public function paginatesInTitleOrderWithPageToken(): void
    {
        $firstId = $this->generateUuid();
        $secondId = $this->generateUuid();
        $thirdId = $this->generateUuid();
        $this->storeEvents(
            $this->createEvent($thirdId, title: 'C ライブ', startOn: '2026-01-01'),
            $this->createEvent($firstId, title: 'A ライブ', startOn: '2026-12-01'),
            $this->createEvent($secondId, title: 'B ライブ', startOn: null),
        );

        $first = $this->get(route(PublicEventRouteMap::List, ['pageSize' => 2]))
            ->assertStatus(200)
            ->assertJsonPath('items.0.eventId', $firstId)
            ->assertJsonPath('items.1.eventId', $secondId);

        $this->get(route(PublicEventRouteMap::List, ['pageSize' => 2, 'pageToken' => $first->json('nextPageToken')]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.eventId', $thirdId)
            ->assertJsonMissingPath('nextPageToken');
    }

    #[Test]
    #[DataProvider('provideInvalidPageTokens')]
    public function rejectsInvalidPageToken(string $pageToken): void
    {
        $this->get(route(PublicEventRouteMap::List, ['pageToken' => $pageToken]))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideInvalidPageTokens(): array
    {
        return [
            'not base64' => ['***'],
            'not json' => [base64_encode('not json')],
            'missing keys' => [base64_encode('{}')],
        ];
    }

    #[Test]
    #[DataProvider('provideOutOfRangePageSizes')]
    public function rejectsOutOfRangePageSize(int $pageSize): void
    {
        $this->get(route(PublicEventRouteMap::List, ['pageSize' => $pageSize]))
            ->assertStatus(422)
            ->assertJsonPath('code', 'validation_failed');
    }

    /**
     * @return array<string, array{int}>
     */
    public static function provideOutOfRangePageSizes(): array
    {
        return [
            'zero' => [0],
            'over max' => [101],
        ];
    }
}
