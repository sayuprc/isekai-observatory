<?php

declare(strict_types=1);

namespace App\Http\Middleware\Public;

use App\Http\Middleware\OpenApiValidator;
use Override;

class PublicOpenApiValidator extends OpenApiValidator
{
    #[Override]
    protected function getPath(): string
    {
        return config()->string('openapi.path.public');
    }

    #[Override]
    protected function getRoutePrefixPattern(): string
    {
        return '#^public/v1#';
    }
}
