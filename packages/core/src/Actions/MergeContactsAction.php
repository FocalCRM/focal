<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Enums\ActivityType;
use Focal\Core\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeContactsAction
{
    /**
     * Merge a secondary duplicate contact into a primary contact.
     * Reparents activities, associations, deals, and tickets, merges properties, and soft-deletes secondary.
     *
     * @param  array<string, mixed>  $fieldOverrides
     */
    public function execute(Contact $primary, Contact $secondary, array $fieldOverrides = []): Contact
    {
        return DB::transaction(function () use ($primary, $secondary, $fieldOverrides): Contact {
            $activitiesTable = config('focal-core.tables.activities', 'focal_activities');
            $associationsTable = config('focal-core.tables.associations', 'focal_associations');

            // 1. Fill empty primary fields from secondary
            $fillableAttributes = ['first_name', 'last_name', 'phone', 'lifecycle_stage', 'lead_status', 'owner_id', 'team_id'];
            foreach ($fillableAttributes as $attr) {
                if (empty($primary->getAttribute($attr)) && ! empty($secondary->getAttribute($attr))) {
                    $primary->setAttribute($attr, $secondary->getAttribute($attr));
                }
            }

            // Apply explicit field overrides
            foreach ($fieldOverrides as $key => $value) {
                $primary->setAttribute($key, $value);
            }

            // Lead score: keep highest score
            $primary->lead_score = max((int) $primary->lead_score, (int) $secondary->lead_score);

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

            // 4. Reparent Deals (if table exists)
            $dealsTable = config('focal-sales.tables.deals', 'focal_deals');
            if (Schema::hasTable($dealsTable) && Schema::hasColumn($dealsTable, 'contact_id')) {
                DB::table($dealsTable)
                    ->where('contact_id', $secondary->id)
                    ->update(['contact_id' => $primary->id]);
            }

            // 5. Reparent Tickets (if table exists)
            $ticketsTable = config('focal-service.tables.tickets', 'focal_tickets');
            if (Schema::hasTable($ticketsTable) && Schema::hasColumn($ticketsTable, 'contact_id')) {
                DB::table($ticketsTable)
                    ->where('contact_id', $secondary->id)
                    ->update(['contact_id' => $primary->id]);
            }

            // 6. Reparent Form Submissions (if table exists)
            $submissionsTable = config('focal-marketing.tables.form_submissions', 'focal_form_submissions');
            if (Schema::hasTable($submissionsTable) && Schema::hasColumn($submissionsTable, 'contact_id')) {
                DB::table($submissionsTable)
                    ->where('contact_id', $secondary->id)
                    ->update(['contact_id' => $primary->id]);
            }

            // 7. Log audit note
            $primary->logActivity(
                type: ActivityType::Note,
                title: 'Contact Merged',
                body: "Merged duplicate contact {$secondary->email} (ID #{$secondary->id}) into this record."
            );

            // 8. Soft-delete secondary record
            $secondary->delete();

            return $primary->fresh() ?? $primary;
        });
    }
}
