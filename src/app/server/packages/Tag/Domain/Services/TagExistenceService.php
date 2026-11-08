<?php

declare(strict_types=1);

namespace Tag\Domain\Services;

use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagChanges;
use Tag\Domain\Models\TagRepositoryInterface;

class TagExistenceService
{
    public function __construct(private readonly TagRepositoryInterface $repository)
    {
    }

    /**
     * 付与するタグが存在することを確かめる。存在しない外部キーを DB に任せて 500 にしないために使う
     *
     * @throws BusinessRuleViolationException
     */
    public function assertAddable(TagChanges $changes): void
    {
        if ($changes->addTagIds === []) {
            return;
        }

        $existing = array_map(
            static fn (Tag $tag): string => $tag->tagId->value,
            $this->repository->findByIds(...$changes->addTagIds),
        );

        foreach ($changes->addTagIds as $tagId) {
            if (! in_array($tagId->value, $existing, true)) {
                throw new BusinessRuleViolationException(sprintf('存在しないタグが指定されています "%s"', $tagId->value));
            }
        }
    }
}
