<?php

declare(strict_types=1);

namespace Focal\Core\Models;

use Carbon\CarbonInterface;
use Focal\Core\Database\Factories\PropertyDefinitionFactory;
use Focal\Core\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $entity_type
 * @property string $name
 * @property string $label
 * @property PropertyType $type
 * @property string $group_name
 * @property array<string, mixed>|null $options
 * @property string|null $description
 * @property bool $is_required
 * @property bool $is_searchable
 * @property int $sort_order
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class PropertyDefinition extends Model
{
    /** @use HasFactory<PropertyDefinitionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'entity_type',
        'name',
        'label',
        'type',
        'group_name',
        'options',
        'description',
        'is_required',
        'is_searchable',
        'sort_order',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('focal-core.tables.properties', 'focal_properties');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'options' => 'array',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope to properties for a specific entity type.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForEntity(Builder $query, string $entityType): Builder
    {
        return $query->where('entity_type', $entityType)->orderBy('sort_order');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PropertyDefinitionFactory
    {
        return PropertyDefinitionFactory::new();
    }
}
