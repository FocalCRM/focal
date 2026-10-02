<?php

declare(strict_types=1);

namespace Odden\Core\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $list_id
 * @property string $member_type
 * @property int $member_id
 * @property CarbonInterface $added_at
 */
class ListMembership extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'list_id',
        'member_type',
        'member_id',
        'added_at',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('odden-core.tables.list_memberships', 'odden_list_memberships');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    /**
     * The parent list.
     *
     * @return BelongsTo<CrmList, $this>
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(CrmList::class, 'list_id');
    }

    /**
     * The member record (Contact, Company, etc.).
     *
     * @return MorphTo<Model, $this>
     */
    public function member(): MorphTo
    {
        return $this->morphTo();
    }
}
