<?php

declare(strict_types=1);

namespace Focal\Core\Tests\Fixtures;

use Closure;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registers `deals` and `tickets` on Contact and Company with resolveRelationUsing(), the way
 * the Sales and Service service providers do, so Core can be tested against dynamic relations
 * without installing those packages.
 */
final class CrossHubRelations
{
    public static function install(): void
    {
        Schema::create('fixture_deals', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('fixture_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('subject');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->boolean('is_sla_response_breached')->default(false);
            $table->boolean('is_sla_resolution_breached')->default(false);
            $table->unsignedTinyInteger('csat_rating')->nullable();
            $table->timestamps();
        });

        foreach ([Contact::class, Company::class] as $model) {
            $model::resolveRelationUsing('deals', fn (Model $record) => $record->belongsToMany(
                Deal::class,
                config('focal-core.tables.associations', 'focal_associations'),
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
        $deal->newQuery()->getConnection()->table(config('focal-core.tables.associations', 'focal_associations'))->insert([
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
