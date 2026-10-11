<?php

declare(strict_types=1);

namespace App\Http\Middleware\Admin;

use App\Http\Middleware\OpenApiValidator;
use Override;

class AdminOpenApiValidator extends OpenApiValidator
{
    #[Override]
    protected function getPath(): string
    {
        return config()->string('openapi.path.admin');
    }

    #[Override]
    protected function getRoutePrefixPattern(): string
    {
        return '#^admin/v1#';
    }
}
