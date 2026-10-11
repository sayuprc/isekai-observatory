<?php

declare(strict_types=1);

namespace Support\App;

enum Environment: string
{
    case Production = 'production';

    case Staging = 'staging';

    case Development = 'development';

    case Local = 'local';

    case Testing = 'testing';

    public static function getEnv(): Environment
    {
        return self::from(config()->string('app.env'));
    }
}
