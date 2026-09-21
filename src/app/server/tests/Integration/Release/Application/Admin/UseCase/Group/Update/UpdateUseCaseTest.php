<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Update;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Group\Update\UpdateUseCase;
use Release\Domain\Models\ReleaseGroupType;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '旧タイトル', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseGroupId: $releaseGroupId,
            title: '新タイトル',
            typeValue: ReleaseGroupType::Single->value,
            description: '更新後の説明',
            isDisplay: false,
            orderNo: 3,
        ));

        $this->assertSame('新タイトル', $result->releaseGroup->title->value);

        $this->assertDatabaseHas('release_groups', [
            'title' => '新タイトル',
            'type' => ReleaseGroupType::Single->value,
            'description' => '更新後の説明',
            'is_display' => false,
            'order_no' => 3,
        ]);
    }

    #[Test]
    public function notFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseGroupId: $this->generateUuid(),
            title: '新タイトル',
            typeValue: ReleaseGroupType::Album->value,
            description: '説明',
            isDisplay: true,
            orderNo: 1,
        ));
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
