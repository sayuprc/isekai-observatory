<?php

declare(strict_types=1);

namespace App\OpenApi;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class CachedValidatorBuilder extends ValidatorBuilder
{
    /**
     * schema を 1 回だけ取得して使い回すため protected な getOrCreateSchema を公開する
     */
    public function getSchema(): OpenApi
    {
        return $this->getOrCreateSchema();
    }
}
