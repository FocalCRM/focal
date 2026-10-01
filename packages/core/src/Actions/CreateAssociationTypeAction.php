<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Enums\AssociationCardinality;
use Focal\Core\Models\AssociationType;

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
