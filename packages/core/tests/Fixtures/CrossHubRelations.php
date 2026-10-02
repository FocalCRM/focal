<?php

declare(strict_types=1);

namespace Odden\Core\Tests\Fixtures;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;

/**
 * Registers `deals` and `tickets` on Contact and Company with resolveRelationUsing(), the way
 * the Sales and Service service providers do, so Core can be tested against dynamic relations
 * without installing those packages. Their tables come from tests/Fixtures/migrations.
 */
final class CrossHubRelations
{
    public static function install(): void
    {
        foreach ([Contact::class, Company::class] as $model) {
            $model::resolveRelationUsing('deals', fn (Model $record) => $record->belongsToMany(
                Deal::class,
                config('odden-core.tables.associations', 'odden_associations'),
                'child_id',
                'parent_id'
            )
                ->wherePivot('parent_type', (new Deal)->getMorphClass())
                ->wherePivot('child_type', $record->getMorphClass()));
        }

        Contact::resolveRelationUsing('tickets', fn (Contact $contact) => $contact->hasMany(Ticket::class, 'contact_id'));
        Company::resolveRelationUsing('tickets', fn (Company $company) => $company->hasMany(Ticket::class, 'company_id'));
    }

    /**
     * Resolvers are static on Model, so remove them or they leak into later tests.
     */
    public static function uninstall(): void
    {
        Closure::bind(static function (): void {
            foreach ([Contact::class, Company::class] as $model) {
                unset(Model::$relationResolvers[$model]['deals'], Model::$relationResolvers[$model]['tickets']);
            }
        }, null, Model::class)();
    }

    /**
     * Link a deal to a contact or company the way Sales does: deal as parent.
     */
    public static function attachDeal(Contact|Company $record, Deal $deal): void
    {
        $deal->newQuery()->getConnection()->table(config('odden-core.tables.associations', 'odden_associations'))->insert([
            'parent_type' => $deal->getMorphClass(),
            'parent_id' => $deal->getKey(),
            'child_type' => $record->getMorphClass(),
            'child_id' => $record->getKey(),
            'type' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
