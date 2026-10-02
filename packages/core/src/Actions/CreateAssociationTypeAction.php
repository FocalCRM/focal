<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Odden\Core\Enums\AssociationCardinality;
use Odden\Core\Models\AssociationType;

class CreateAssociationTypeAction
{
    /**
     * Create or register an association type definition.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): AssociationType
    {
        if (isset($attributes['cardinality']) && is_string($attributes['cardinality'])) {
            $attributes['cardinality'] = AssociationCardinality::from($attributes['cardinality']);
        }

        return AssociationType::create($attributes);
    }
}
