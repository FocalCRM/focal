<?php

declare(strict_types=1);

namespace Focal\Core\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $parent_type
 * @property int $parent_id
 * @property string $child_type
 * @property int $child_id
 * @property string $type
 * @property int|null $association_type_id
 * @property string|null $label
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read AssociationType|null $associationType
 */
class Association extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_type',
        'parent_id',
        'child_type',
        'child_id',
        'type',
        'association_type_id',
        'label',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('focal-core.tables.associations', 'focal_associations');
    }

    /**
     * Get the parent model of the association.
     *
     * @return MorphTo<Model, $this>
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the child model of the association.
     *
     * @return MorphTo<Model, $this>
     */
    public function child(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The defined association type with semantic labels and cardinality rules.
     *
     * @return BelongsTo<AssociationType, $this>
     */
    public function associationType(): BelongsTo
    {
        return $this->belongsTo(
            AssociationType::class,
            'association_type_id'
        );
    }
}
