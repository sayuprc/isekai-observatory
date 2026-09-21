<?php

declare(strict_types=1);

namespace App\Http\Middleware\Viewer;

use App\Http\Middleware\OpenApiValidator;
use Override;

class ViewerOpenApiValidator extends OpenApiValidator
{
    #[Override]
    protected function getPath(): string
    {
        return config()->string('openapi.path.viewer');
    }

    #[Override]
    protected function getRoutePrefixPattern(): string
    {
        return '#^v1#';
    }
}
