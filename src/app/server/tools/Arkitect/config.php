<?php

declare(strict_types=1);

use Tools\Arkitect\PackageRules;

// 規則の意味は ADR-0044 を参照する
return new PackageRules(
    // 列ごとに下の段から並べる
    // 上の段のパッケージは、同じ列の下の段のパッケージの Domain と Support にだけ依存してよい
    columns: [
        // 業務パッケージと、その読み取り
        [
            ['Tag', 'Person', 'Venue'],
            ['Media'],
            ['Song'],
            ['Release'],
            ['Event'],
            ['Viewer', 'Public'],
        ],
        // 管理者パッケージ
        [
            ['AdminUser'],
            ['Auth'],
        ],
    ],
    // Support の区分ごとに、自分のほかに依存してよい名前空間
    // ここにない区分は、パッケージと app に依存しない限り自由とする
    kernel: [
        'Support\Domain' => ['DateType'],
        'Support\Contracts' => [],
        'Support\Optional' => [],
        'Support\Collection' => [],
        'Support\Pagination' => ['Support\Domain'],
        'Support\UseCase' => ['Support\Contracts', 'Support\Domain', 'Support\Optional'],
    ],
    // 各パッケージの層が、自分の層と下のパッケージの Domain のほかに依存してよい名前空間
    domain: [
        'Support\Domain',
        'Support\Contracts',
        'Support\Collection',
        'Support\Optional',
        'DateType',
    ],
    application: [
        'Support\Domain',
        'Support\Contracts',
        'Support\Collection',
        'Support\Optional',
        'Support\Pagination',
        'Support\UseCase',
        'Support\Notification\Contracts',
        'Support\Notification\NotificationService',
        'DateType',
    ],
    query: [
        'Support\Domain',
        'Support\Pagination',
    ],
);
