<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase\Tag;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Create\CreateInputData;
use Song\Application\Admin\UseCase\Tag\Create\CreateUseCase;
use Song\Infrastructures\Tag\SongTagRepository;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('テストタグA'));

        $tags = DB::table('song_tags')->orderBy('order_no')->get();
        $this->assertCount(1, $tags);
        $this->assertSame('テストタグA', $tags->first()->name);
        $this->assertSame(10, (int)$tags->first()->order_no);

        $tagId = $this->toUuid($tags->first()->song_tag_id);
        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Create, AuditTargetType::SongTag, $tagId);
        $this->assertSame('テストタグA', $log['snapshot']['name']);
    }

    #[Test]
    public function createAppendsOrderNoFromCurrentMax(): void
    {
        $this->app->make(SongTagRepository::class)->save(
            $this->createSongTag($this->generateUuid(), '既存タグ', 40),
        );

        $result = $this->getInstance()->handle(new CreateInputData('テストタグA'));

        $tags = DB::table('song_tags')->orderBy('order_no')->get();
        $this->assertCount(2, $tags);
        $this->assertSame('テストタグA', $tags->last()->name);
        $this->assertSame(50, (int)$tags->last()->order_no);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
