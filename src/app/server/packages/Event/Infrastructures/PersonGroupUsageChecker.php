<?php

declare(strict_types=1);

namespace Event\Infrastructures;

use Override;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Services\PersonGroupUsageCheckerInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class PersonGroupUsageChecker implements PersonGroupUsageCheckerInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function isUsed(PersonGroupId $personGroupId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('song_performance_persons')
                ->where('person_group_id', '=', $this->converter->toBin($personGroupId->value))
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }
}
