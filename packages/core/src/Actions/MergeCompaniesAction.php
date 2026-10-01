<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Enums\ActivityType;
use Focal\Core\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeCompaniesAction
{
    /**
     * Merge a secondary duplicate company into a primary company.
     * Reparents activities, associations, contacts, and tickets, merges properties, and soft-deletes secondary.
     *
     * @param  array<string, mixed>  $fieldOverrides
     */
    public function execute(Company $primary, Company $secondary, array $fieldOverrides = []): Company
    {
        return DB::transaction(function () use ($primary, $secondary, $fieldOverrides): Company {
            $activitiesTable = config('focal-core.tables.activities', 'focal_activities');
            $associationsTable = config('focal-core.tables.associations', 'focal_associations');

            // 1. Fill empty primary fields from secondary
            $fillableAttributes = ['domain', 'phone', 'industry', 'account_tier', 'owner_id', 'team_id'];
            foreach ($fillableAttributes as $attr) {
                if (empty($primary->getAttribute($attr)) && ! empty($secondary->getAttribute($attr))) {
                    $primary->setAttribute($attr, $secondary->getAttribute($attr));
                }
            }

            // Apply explicit field overrides
            foreach ($fieldOverrides as $key => $value) {
                $primary->setAttribute($key, $value);
            }

            // Intent score: take higher score
            $primary->intent_score = max((int) $primary->intent_score, (int) $secondary->intent_score);
            $primary->intent_surge = $primary->intent_surge || $secondary->intent_surge;

            // Health score: take average
            $primary->health_score = (int) round(((int) $primary->health_score + (int) $secondary->health_score) / 2);

            // Merge custom properties
            $primaryProps = $primary->properties ?? [];
            $secondaryProps = $secondary->properties ?? [];
            $primary->properties = array_merge($secondaryProps, $primaryProps);

            $primary->save();

            // 2. Move Activities
            DB::table($activitiesTable)
                ->where('subject_type', $secondary->getMorphClass())
                ->where('subject_id', $secondary->id)
                ->update(['subject_id' => $primary->id]);

            // 3. Move Associations (Parent)
            $secondaryParentAssocs = DB::table($associationsTable)
                ->where('parent_type', $secondary->getMorphClass())
                ->where('parent_id', $secondary->id)
                ->get();

            foreach ($secondaryParentAssocs as $assoc) {
                $exists = DB::table($associationsTable)
                    ->where('parent_type', $primary->getMorphClass())
                    ->where('parent_id', $primary->id)
                    ->where('child_type', $assoc->child_type)
                    ->where('child_id', $assoc->child_id)
                    ->where('type', $assoc->type)
                    ->exists();

                if (! $exists) {
                    DB::table($associationsTable)
                        ->where('id', $assoc->id)
                        ->update(['parent_id' => $primary->id]);
                } else {
                    DB::table($associationsTable)->where('id', $assoc->id)->delete();
                }
            }

            // Move Associations (Child)
            $secondaryChildAssocs = DB::table($associationsTable)
                ->where('child_type', $secondary->getMorphClass())
                ->where('child_id', $secondary->id)
                ->get();

            foreach ($secondaryChildAssocs as $assoc) {
                $exists = DB::table($associationsTable)
                    ->where('parent_type', $assoc->parent_type)
                    ->where('parent_id', $assoc->parent_id)
                    ->where('child_type', $primary->getMorphClass())
                    ->where('child_id', $primary->id)
                    ->where('type', $assoc->type)
                    ->exists();

                if (! $exists) {
                    DB::table($associationsTable)
                        ->where('id', $assoc->id)
                        ->update(['child_id' => $primary->id]);
                } else {
                    DB::table($associationsTable)->where('id', $assoc->id)->delete();
                }
            }

            // 4. Reparent Tickets (if table exists)
            $ticketsTable = config('focal-service.tables.tickets', 'focal_tickets');
            if (Schema::hasTable($ticketsTable) && Schema::hasColumn($ticketsTable, 'company_id')) {
                DB::table($ticketsTable)
                    ->where('company_id', $secondary->id)
                    ->update(['company_id' => $primary->id]);
            }

            // 5. Log audit note
            $primary->logActivity(
                type: ActivityType::Note,
                title: 'Company Merged',
                body: "Merged duplicate company {$secondary->name} (ID #{$secondary->id}) into this record."
            );

            // 6. Recalculate Health Score on master
            if (class_exists(CalculateCustomerHealthScoreAction::class)) {
                app(CalculateCustomerHealthScoreAction::class)->execute($primary);
            }

            // 7. Soft-delete secondary record
            $secondary->delete();

            return $primary->fresh() ?? $primary;
        });
    }
}
