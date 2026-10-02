<?php

declare(strict_types=1);

namespace Odden\Core\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Looks up a relation that another Odden package may add to a Core model.
 *
 * Sales and Service add `deals` and `tickets` with resolveRelationUsing(), which
 * method_exists() can't see. Model::isRelation() checks both declared methods and
 * registered resolvers, so Core can use those relations without depending on the packages.
 */
final class OptionalRelation
{
    /**
     * The relation named $name on $model, or null when no package has registered it.
     *
     * @return Relation<Model, Model, mixed>|null
     */
    public static function on(Model $model, string $name): ?Relation
    {
        if (! $model->isRelation($name)) {
            return null;
        }

        $relation = $model->{$name}();

        return $relation instanceof Relation ? $relation : null;
    }
}
