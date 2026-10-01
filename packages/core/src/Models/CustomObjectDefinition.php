<?php

declare(strict_types=1);

namespace Focal\Core\Models;

use Carbon\CarbonInterface;
use Focal\Core\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $singular_label
 * @property string $plural_label
 * @property string|null $description
 * @property string $primary_display_property
 * @property list<string>|null $secondary_display_properties
 * @property string|null $icon
 * @property int|null $team_id
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class CustomObjectDefinition extends Model
{
    use BelongsToTeam;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'singular_label',
        'plural_label',
        'description',
        'primary_display_property',
        'secondary_display_properties',
        'icon',
        'team_id',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('focal-core.tables.custom_object_definitions', 'focal_custom_object_definitions');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'secondary_display_properties' => 'array',
        ];
    }

    /**
     * Records created under this custom object schema.
     *
     * @return HasMany<CustomObjectRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(
            CustomObjectRecord::class,
            'definition_id'
        );
    }
}
