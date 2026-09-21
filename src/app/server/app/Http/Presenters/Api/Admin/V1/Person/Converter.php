<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use OpenAPI\Admin\Client\Model\Person as OpenApiPerson;
use Person\Domain\Models\Person;

class Converter
{
    public function toOpenApiPerson(Person $person): OpenApiPerson
    {
        return new OpenApiPerson()
            ->setPersonId($person->personId->value)
            ->setName($person->name->value)
            ->setOrderNo($person->orderNo->value);
    }
}
