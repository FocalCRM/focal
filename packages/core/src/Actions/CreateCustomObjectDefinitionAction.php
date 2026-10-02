<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Odden\Core\Events\CustomObjectDefinitionCreated;
use Odden\Core\Models\CustomObjectDefinition;
use Illuminate\Support\Str;

class CreateCustomObjectDefinitionAction
{
    /**
     * Create or register a custom object schema definition.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): CustomObjectDefinition
    {
        if (isset($attributes['name'])) {
            $attributes['name'] = Str::slug((string) $attributes['name'], '_');
        }

        if (! isset($attributes['plural_label']) && isset($attributes['singular_label'])) {
            $attributes['plural_label'] = Str::plural((string) $attributes['singular_label']);
        }

        /** @var CustomObjectDefinition $definition */
        $definition = CustomObjectDefinition::create($attributes);

        event(new CustomObjectDefinitionCreated($definition));

        return $definition;
    }
}
