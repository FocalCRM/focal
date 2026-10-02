<?php

declare(strict_types=1);

namespace Odden\Core\Models;

use Carbon\CarbonInterface;
use Odden\Core\Enums\AssociationCardinality;
use Odden\Core\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $reverse_label
 * @property AssociationCardinality $cardinality
 * @property string|null $from_record_type
 * @property string|null $to_record_type
 * @property bool $is_system
 * @property int|null $team_id
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class AssociationType extends Model
{
    use BelongsToTeam;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'label',
        'reverse_label',
        'cardinality',
        'from_record_type',
        'to_record_type',
        'is_system',
        'team_id',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('odden-core.tables.association_types', 'odden_association_types');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cardinality' => AssociationCardinality::class,
            'is_system' => 'boolean',
        ];
    }

    /**
     * Associations of this type.
     *
     * @return HasMany<Association, $this>
     */
    public function associations(): HasMany
    {
        return $this->hasMany(
            Association::class,
            'association_type_id'
        );
    }

    /**
     * Resolve the appropriate label based on the viewing record.
     */
    public function getLabelFor(Model $record): string
    {
        if ($this->reverse_label !== null && $this->to_record_type !== null && $record->getMorphClass() === $this->to_record_type) {
            return $this->reverse_label;
        }

        return $this->label;
    }
}
