<?php

declare(strict_types=1);

namespace Tag\Domain\Services;

use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagName;
use Tag\Domain\Models\TagRepositoryInterface;

class TagIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly TagRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(string $name): Tag
    {
        $tag = $this->build(
            $this->generator->generate(),
            $name,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->repository->getMaxOrderNo() + 10,
        );

        if ($this->repository->findByName($tag->name) !== null) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $tag;
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(string $tagId, string $name, int $orderNo): Tag
    {
        $tag = $this->build($tagId, $name, $orderNo);

        if (($found = $this->repository->findByName($tag->name)) !== null && ! $found->equals($tag)) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $tag;
    }

    private function build(string $tagId, string $name, int $orderNo): Tag
    {
        return new Tag(new TagId($tagId), new TagName($name), new OrderNo($orderNo));
    }
}
