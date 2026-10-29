<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Application\Admin\UseCase;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tag\Application\Admin\Query\TagUsageCountQueryServiceInterface;
use Tag\Application\Admin\UseCase\Search\SearchInputData;
use Tag\Application\Admin\UseCase\Search\SearchUseCase;
use Tag\Domain\Criteria\TagSearchCriteria;
use Tag\Domain\Models\TagRepositoryInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SearchUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TagRepositoryInterface $repository;

    private MockInterface&TagUsageCountQueryServiceInterface $usageCountQueryService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TagRepositoryInterface::class);
        $this->usageCountQueryService = Mockery::mock(TagUsageCountQueryServiceInterface::class);
        $this->usageCountQueryService->shouldReceive('countUsages')->andReturn([]);
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $tag = $this->createTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'テストタグA', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(static fn (TagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn([$tag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(static fn (TagSearchCriteria $criteria): bool => $criteria->name->isEmpty())
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData());

        $output = $result;
        $this->assertCount(1, $output->tags);
        $this->assertSame(1, $output->maxPage);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $output->tags[0]->tagId->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $tag = $this->createTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'テストタグA', 1);

        $this->repository->shouldReceive('search')
            ->withArgs(
                static fn (TagSearchCriteria $criteria): bool => (
                    $criteria->name->isPresent() && $criteria->name->get() === 'テスト'
                ),
            )
            ->andReturn([$tag])
            ->once();

        $this->repository->shouldReceive('maxPage')
            ->withArgs(
                static fn (TagSearchCriteria $criteria): bool => (
                    $criteria->name->isPresent() && $criteria->name->get() === 'テスト'
                ),
            )
            ->andReturn(1)
            ->once();

        $result = $this->getInstance()->handle(new SearchInputData(name: 'テスト'));

        $output = $result;
        $this->assertCount(1, $output->tags);
        $this->assertSame('テストタグA', $output->tags[0]->name->value);
    }

    #[Test]
    public function returnAuthenticationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

        $context = $this->app->make(AuthContext::class);

        $this->expectException(UnauthenticatedException::class);

        $result = new SearchUseCase(
            $this->authorizer($context),
            $this->repository,
            $this->usageCountQueryService,
        )->handle(new SearchInputData());
    }

    #[Test]
    public function returnAuthorizationError(): void
    {
        $this->repository->shouldNotReceive('search');
        $this->repository->shouldNotReceive('maxPage');

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

        $result = new SearchUseCase(
            $this->authorizer($context),
            $this->repository,
            $this->usageCountQueryService,
        )->handle(new SearchInputData());
    }

    private function getInstance(): SearchUseCase
    {
        return new SearchUseCase(
            $this->authorizer(),
            $this->repository,
            $this->usageCountQueryService,
        );
    }
}
