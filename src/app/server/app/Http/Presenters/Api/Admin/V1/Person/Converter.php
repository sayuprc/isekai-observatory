<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Person;

use OpenAPI\Admin\Client\Model\Person as OpenApiPerson;
use OpenAPI\Admin\Client\Model\PersonSummary as OpenApiPersonSummary;
use Person\Application\Admin\Query\PersonUsageCount;
use Person\Domain\Models\Person;

class Converter
{
    public function toOpenApiPersonSummary(Person $person, PersonUsageCount $usage): OpenApiPersonSummary
    {
        return new OpenApiPersonSummary()
            ->setPersonId($person->personId->value)
            ->setName($person->name->value)
            ->setOrderNo($person->orderNo->value)
            ->setSongCount($usage->songCount)
            ->setPerformanceCount($usage->performanceCount);
    }

    public function toOpenApiPerson(Person $person): OpenApiPerson
    {
        return new OpenApiPerson()
            ->setPersonId($person->personId->value)
            ->setName($person->name->value)
            ->setOrderNo($person->orderNo->value);
    }
}
