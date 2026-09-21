<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Create\CreateInputData;
use Release\Application\Admin\UseCase\Group\Create\CreateUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\Domain\Exceptions\InvalidDomainException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canCreate(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseGroupType::Album->value,
            description: '1st アルバム',
            isDisplay: true,
            orderNo: 1,
        ));

        $this->assertSame('観測された春', $result->releaseGroup->title->value);

        $this->assertDatabaseHas('release_groups', [
            'title' => '観測された春',
            'type' => ReleaseGroupType::Album->value,
            'description' => '1st アルバム',
            'is_display' => true,
            'order_no' => 1,
        ]);
    }

    #[Test]
    public function usesSubmittedOrderNo(): void
    {
        $this->storeReleaseGroups(
            $this->createReleaseGroup($this->generateUuid(), '既存の作品', ReleaseGroupType::Single, true, orderNo: 15),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseGroupType::Album->value,
            description: '',
            isDisplay: true,
            orderNo: 7,
        ));

        $this->assertSame(7, $result->releaseGroup->orderNo->value);
    }

    #[Test]
    public function createFailsWhenTypeIsInvalid(): void
    {
        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: 0,
            description: '',
            isDisplay: true,
            orderNo: 1,
        ));
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
