<?php

declare(strict_types=1);

return [
    'cache_path' => base_path('bootstrap/cache/valinor'),

    /**
     * MapperInterface::map() に渡される型
     * warmupCacheFor() は再帰的にプロパティ型もキャッシュする
     */
    'warmup' => [
        \Auth\Application\Admin\Login\LoginFinishInputData::class,
        \Auth\Application\Admin\Login\LoginStartInputData::class,
        \Auth\Application\Admin\Recovery\RecoveryFinishInputData::class,
        \Auth\Application\Admin\Recovery\RecoveryStartInputData::class,
        \Auth\Application\Admin\Refresh\RefreshInputData::class,
        \Auth\Application\Admin\RegisterFinish\RegisterFinishInputData::class,
        \Auth\Application\Admin\RegisterStart\RegisterStartInputData::class,
        \Auth\Domain\Services\Token\AccessToken\AccessTokenPayload::class,
        \Event\Application\Admin\BulkUpdate\BulkUpdateInputData::class,
        \Event\Application\Admin\Create\CreateInputData::class,
        \Event\Application\Admin\Search\SearchInputData::class,
        \Event\Application\Admin\Update\UpdateInputData::class,
        \Media\Application\Admin\BulkUpdate\BulkUpdateInputData::class,
        \Media\Application\Admin\Create\CreateInputData::class,
        \Media\Application\Admin\Search\SearchInputData::class,
        \Media\Application\Admin\Update\UpdateInputData::class,
        \Person\Application\Admin\Create\CreateInputData::class,
        \Person\Application\Admin\Group\Create\CreateInputData::class,
        \Person\Application\Admin\Group\Search\SearchInputData::class,
        \Person\Application\Admin\Group\Update\UpdateInputData::class,
        \Person\Application\Admin\Search\SearchInputData::class,
        \Person\Application\Admin\Update\UpdateInputData::class,
        \Release\Application\Admin\Create\CreateInputData::class,
        \Release\Application\Admin\Group\BulkUpdate\BulkUpdateInputData::class,
        \Release\Application\Admin\Group\Create\CreateInputData::class,
        \Release\Application\Admin\Group\Search\SearchInputData::class,
        \Release\Application\Admin\Group\Update\UpdateInputData::class,
        \Release\Application\Admin\Search\SearchInputData::class,
        \Release\Application\Admin\Update\UpdateInputData::class,
        \Song\Application\Admin\BulkUpdate\BulkUpdateInputData::class,
        \Song\Application\Admin\Create\CreateInputData::class,
        \Song\Application\Admin\Search\SearchInputData::class,
        \Song\Application\Admin\Update\UpdateInputData::class,
        \Support\UseCase\AuditLog\Search\SearchInputData::class,
        \Tag\Application\Admin\Create\CreateInputData::class,
        \Tag\Application\Admin\Search\SearchInputData::class,
        \Tag\Application\Admin\Update\UpdateInputData::class,
        \Venue\Application\Admin\Create\CreateInputData::class,
        \Venue\Application\Admin\Search\SearchInputData::class,
        \Venue\Application\Admin\Update\UpdateInputData::class,
    ],
];
