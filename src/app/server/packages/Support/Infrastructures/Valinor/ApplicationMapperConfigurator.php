<?php

declare(strict_types=1);

namespace Support\Infrastructures\Valinor;

use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;
use CuyZ\Valinor\MapperBuilder;
use Override;

final class ApplicationMapperConfigurator implements MapperBuilderConfigurator
{
    #[Override]
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->allowSuperfluousKeys()
            ->allowScalarValueCasting()
            ->supportDateFormats('Y-m-d', 'Y-m-d H:i:s');
    }
}
