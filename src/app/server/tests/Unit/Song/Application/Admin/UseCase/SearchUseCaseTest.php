<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\Query\SongQueryServiceInterface;
use Song\Application\Admin\Query\SongSummary;
use Song\Application\Admin\UseCase\Search\SearchInputData;
use Song\Application\Admin\UseCase\Search\SearchUseCase;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\SongType;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongQueryServiceInterface $query;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = Mockery::mock(SongQueryServiceInterface::class);
    }

    #[Test]
    public function searchWithoutFilters(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'テスト楽曲',
            SongType::Original,
            true,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->isDisplay->isEmpty())
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->title->isEmpty() && $criteria->type->isEmpty() && $criteria->isDisplay->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $output = $result;
        $this->assertCount(1, $output->songs);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->songs[0]->songId);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'テスト楽曲',
            SongType::Original,
            true,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === 'テスト楽曲')
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->title->isPresent() && $criteria->title->get() === 'テスト楽曲')
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(title: 'テスト楽曲'));

        $output = $result;
        $this->assertCount(1, $output->songs);
        $this->assertSame('テスト楽曲', $output->songs[0]->title);
    }

    #[Test]
    public function searchWithType(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'テスト楽曲',
            SongType::Original,
            true,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->type->isPresent() && $criteria->type->get() === SongType::Original)
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(type: SongType::Original->value));

        $output = $result;
        $this->assertCount(1, $output->songs);
        $this->assertSame(SongType::Original, $output->songs[0]->type);
    }

    #[Test]
    public function searchWithIsDisplay(): void
    {
        $summary = new SongSummary(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'テスト楽曲',
            SongType::Original,
            false,
            1,
        );

        $this->query->shouldReceive('search')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->isDisplay->isPresent() && $criteria->isDisplay->get() === false)
            ->andReturn([$summary])
            ->once();

        $this->query->shouldReceive('maxPage')
            ->withArgs(static fn (SongSearchCriteria $criteria): bool => $criteria->isDisplay->isPresent() && $criteria->isDisplay->get() === false)
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(isDisplay: false));

        $output = $result;
        $this->assertCount(1, $output->songs);
        $this->assertFalse($output->songs[0]->isDisplay);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->query->shouldNotReceive('search');
        $this->query->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $this->expectException(UnauthenticatedException::class);

        $result = new SearchUseCase($this->authorizer($context), $this->query)->handle(new SearchInputData());
    }

    #[Test]
    public function returnAuthorizationError(): void
    {
        $this->query->shouldNotReceive('search');
        $this->query->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $this->expectException(PermissionDeniedException::class);

        $result = new SearchUseCase($this->authorizer($context), $this->query)->handle(new SearchInputData());
    }

    private function getInstance(): SearchUseCase
    {
        return new SearchUseCase(
            $this->authorizer(),
            $this->query,
        );
    }
}
