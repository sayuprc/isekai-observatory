<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Media\Domain\Models\MediaType;
use Media\Route\MediaRouteMap;
use Person\Route\PersonRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Venue\Domain\Models\VenueKind;
use Venue\Route\VenueRouteMap;

class ReferencedEntityDeleteTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function rejectsDeletingSongUsedByPerformance(): void
    {
        [$songId] = $this->storeEventWithReferences();

        $this->withAuth()->delete(route(SongRouteMap::Delete, $songId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsDeletingPersonUsedAsCoVocalist(): void
    {
        [, $personId] = $this->storeEventWithReferences();

        $this->withAuth()->delete(route(PersonRouteMap::Delete, $personId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsDeletingVenueUsedByEvent(): void
    {
        [, , $venueId] = $this->storeEventWithReferences();

        $this->withAuth()->delete(route(VenueRouteMap::Delete, $venueId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    #[Test]
    public function rejectsDeletingMediaUsedByEvent(): void
    {
        [, , , $mediaId] = $this->storeEventWithReferences();

        $this->withAuth()->delete(route(MediaRouteMap::Delete, $mediaId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    /** @return array{0: string, 1: string, 2: string, 3: string} */
    private function storeEventWithReferences(): array
    {
        $songId = $this->generateUuid();
        $personId = $this->generateUuid();
        $venueId = $this->generateUuid();
        $mediaId = $this->generateUuid();
        $performanceId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $this->storePersons($this->createPerson($personId, '共演者', 1));
        $this->storeVenues($this->createVenue($venueId, '会場', VenueKind::Physical));
        $this->storeMedia($this->createMedia($mediaId, '配信', 'https://example.com/stream', MediaType::LiveStream, true));
        $this->storeEvents($this->createEvent(
            $this->generateUuid(),
            venues: [['venueId' => $venueId, 'orderNo' => 1]],
            media: [['mediaId' => $mediaId, 'orderNo' => 1]],
            performances: [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => [['personId' => $personId, 'creditName' => null, 'orderNo' => 1]]]],
        ));

        return [$songId, $personId, $venueId, $mediaId];
    }
}
