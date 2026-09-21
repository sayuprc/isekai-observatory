<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase\Type;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Type\ListUseCase;
use Song\Domain\Models\SongType;
use Tests\TestCase;

class ListUseCaseTest extends TestCase
{
    #[Test]
    public function nonEmptySongTypes(): void
    {
        $result = $this->getInstance()->handle();

        $this->assertCount(2, $types = $result->types);
        $this->assertSame(SongType::cases(), $types);
    }

    private function getInstance(): ListUseCase
    {
        return new ListUseCase($this->authorizer());
    }
}
