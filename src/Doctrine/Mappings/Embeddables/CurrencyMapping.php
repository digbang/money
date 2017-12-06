<?php

namespace Digbang\Money\Doctrine\Mappings\Embeddables;

use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;
use Money\Currency;

class CurrencyMapping extends EmbeddableMapping
{
    /**
     * Returns the fully qualified name of the class that this mapper maps.
     *
     * @return string
     */
    public function mapFor()
    {
        return Currency::class;
    }

    /**
     * Load the object's metadata through the Metadata Builder object.
     *
     * @param Fluent $builder
     */
    public function map(Fluent $builder)
    {
        $builder->text('code');
    }
}
