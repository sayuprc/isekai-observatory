<?php

declare(strict_types=1);

namespace Support\Infrastructures\Valinor;

use AdminUser\Domain\Models\AdminUser;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;
use CuyZ\Valinor\MapperBuilder;
use Override;
use Person\Domain\Models\Person;
use Song\Domain\Models\Song;

final class ApplicationMapperConfigurator implements MapperBuilderConfigurator
{
    #[Override]
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->registerConstructor(AdminUser::reconstruct(...))
            ->registerConstructor(RefreshToken::reconstruct(...))
            ->registerConstructor(Person::reconstruct(...))
            ->registerConstructor(Song::reconstruct(...))
            ->allowSuperfluousKeys()
            ->allowScalarValueCasting()
            ->supportDateFormats('Y-m-d', 'Y-m-d H:i:s');
    }
}
