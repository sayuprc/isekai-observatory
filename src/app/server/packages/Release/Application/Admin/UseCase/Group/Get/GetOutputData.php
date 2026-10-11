<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get;

use Release\Application\Admin\Query\ReleaseGroupReferencedRelease;
use Release\Domain\Models\ReleaseGroup;

readonly class GetOutputData
{
    /**
     * @param list<ReleaseGroupReferencedRelease> $releases
     */
    public function __construct(
        public ReleaseGroup $releaseGroup,
        public array $releases,
    ) {
    }
}
