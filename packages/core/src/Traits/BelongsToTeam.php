<?php

declare(strict_types=1);

namespace Odden\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTeam
{
    /**
     * Scope query to records belonging to a specific team.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }
}
