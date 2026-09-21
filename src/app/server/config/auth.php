<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'custom',
        ],

        // 'users' => [
        //     'driver' => 'eloquent',
        //     'model' => env('AUTH_MODEL', User::class),
        // ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    'jwt' => [
        'alg' => env('AUTH_JWT_ALG', 'HS256'),
        'key' => env('AUTH_JWT_KEY'),
    ],

    'passkey' => [
        'rp_name' => env('AUTH_PASSKEY_RP_NAME', env('APP_NAME', 'IsekaiObservatory')),
        'rp_id' => env('AUTH_PASSKEY_RP_ID', 'local.admin.isekaijoucho.fan'),
        'origin' => env('AUTH_PASSKEY_ORIGIN', 'https://local.admin.isekaijoucho.fan'),
        'timeout_ms' => (int)env('AUTH_PASSKEY_TIMEOUT_MS', 60000),
        'ceremony_ttl_seconds' => (int)env('AUTH_PASSKEY_CEREMONY_TTL_SECONDS', 300),
        'ceremony_cache_store' => env('AUTH_PASSKEY_CEREMONY_CACHE_STORE', 'redis'),

        /*
        | 認証系エンドポイントのレート制限 (1 分あたりの試行回数)
        |
        | API は通常 BFF 経由で呼ばれ送信元 IP が単一になるため、IP ではなく
        | メールや ceremony / refresh token といった資源キーで制限する
        | 公開境界 (BFF) 側の IP 単位制限と合わせた多層防御の内側を担う
        */
        'rate_limit' => [
            'login' => (int)env('AUTH_PASSKEY_RATE_LIMIT_LOGIN', 10),
            'register' => (int)env('AUTH_PASSKEY_RATE_LIMIT_REGISTER', 5),
            'recovery' => (int)env('AUTH_PASSKEY_RATE_LIMIT_RECOVERY', 10),
            'refresh' => (int)env('AUTH_PASSKEY_RATE_LIMIT_REFRESH', 30),
        ],
    ],

    'recovery_code' => [
        /*
        | リカバリーコードのハッシュ化に使うアプリ側秘密値 (pepper)
        | リカバリーコードはエントロピーが小さいため、DB に載らないこの秘密値を鍵にした
        | HMAC でハッシュ化し、DB 単独漏洩時のオフライン総当たりを防ぐ
        | 各環境で十分に長いランダム値を必ず設定すること
        */
        'pepper' => env('AUTH_RECOVERY_CODE_PEPPER'),
    ],
];
