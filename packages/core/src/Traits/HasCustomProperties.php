<?php

declare(strict_types=1);

namespace Odden\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCustomProperties
{
    /**
     * Initialize custom properties attribute casting.
     */
    public function initializeHasCustomProperties(): void
    {
        $this->mergeCasts([
            'properties' => 'array',
        ]);
    }

    /**
     * Retrieve a specific custom property value.
     */
    public function getProperty(string $name, mixed $default = null): mixed
    {
        $properties = $this->properties ?? [];

        return data_get($properties, $name, $default);
    }

    /**
     * Set a custom property value.
     */
    public function setProperty(string $name, mixed $value): static
    {
        $properties = $this->properties ?? [];
        $properties[$name] = $value;
        $this->properties = $properties;

        return $this;
    }

    /**
     * Merge multiple custom properties.
     *
     * @param  array<string, mixed>  $properties
     */
    public function setProperties(array $properties): static
    {
        $existing = $this->properties ?? [];
        $this->properties = array_merge($existing, $properties);

        return $this;
    }

    /**
     * Scope query to records matching a custom property value.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWhereProperty(Builder $query, string $name, mixed $value): Builder
    {
        return $query->where("properties->{$name}", $value);
    }
}
