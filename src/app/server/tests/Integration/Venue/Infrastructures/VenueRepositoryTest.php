<?php

declare(strict_types=1);

namespace Tests\Integration\Venue\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Venue\Domain\Models\VenueKind;
use Venue\Infrastructures\VenueRepository;

class VenueRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function findByIds(): void
    {
        $repository = $this->getInstance();
        $venue1 = $this->createVenue($this->generateUuid(), '会場1', VenueKind::Physical);
        $venue2 = $this->createVenue($this->generateUuid(), '会場2', VenueKind::Online);
        $venue3 = $this->createVenue($this->generateUuid(), '会場3', VenueKind::Physical);
        $repository->save($venue1);
        $repository->save($venue2);
        $repository->save($venue3);

        $this->assertEqualsCanonicalizing([$venue1, $venue3], $repository->findByIds($venue1->venueId, $venue3->venueId));
    }

    #[Test]
    public function findByIdsReturnsEmptyWhenNoIdsGiven(): void
    {
        $this->assertSame([], $this->getInstance()->findByIds());
    }

    private function getInstance(): VenueRepository
    {
        return $this->app->make(VenueRepository::class);
    }
}
