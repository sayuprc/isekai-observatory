<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Admin\UseCase;

use AdminUser\Application\Admin\UseCase\List\ListUseCase;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListUseCaseTest extends TestCase
{
    use EntityFactory;

    private AdminUserRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(AdminUserRepositoryInterface::class);
    }

    #[Test]
    public function emptyAdminUsers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->getInstance()->handle();

        $this->assertCount(0, $response->adminUsers);
    }

    #[Test]
    public function nonEmptyAdminUsers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                $this->createAdminUser('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'admin-a@example.com'),
                $this->createAdminUser('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'admin-b@example.com'),
            ])
            ->once();

        $response = $this->getInstance()->handle();

        $this->assertCount(2, $response->adminUsers);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->adminUsers[0]->adminUserId->value);
        $this->assertSame('admin-a@example.com', $response->adminUsers[0]->email->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->adminUsers[1]->adminUserId->value);
        $this->assertSame('admin-b@example.com', $response->adminUsers[1]->email->value);
    }

    private function getInstance(): ListUseCase
    {
        return new ListUseCase(
            $this->authorizer(),
            $this->repository,
        );
    }
}
