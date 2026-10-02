<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Enums\ActivityType;
use Focal\Core\Events\ContactsMerged;
use Focal\Core\Models\Contact;
use Focal\Core\Support\RecordMerger;
use Illuminate\Support\Facades\DB;

class MergeContactsAction
{
    public function __construct(
        protected RecordMerger $merger
    ) {}

    /**
     * Merge a secondary duplicate contact into a primary contact, inside a transaction.
     * Merges fields and properties, moves everything Core owns (activities, associations, list
     * memberships, property history, lifecycle transitions) to the primary, dispatches
     * ContactsMerged so other packages can move their records, and soft-deletes the secondary.
     *
     * @param  array<string, mixed>  $fieldOverrides
     */
    public function execute(Contact $primary, Contact $secondary, array $fieldOverrides = []): Contact
    {
        return DB::transaction(function () use ($primary, $secondary, $fieldOverrides): Contact {
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

            // Lifecycle stage dates: keep the earliest
            $this->merger->keepEarliestStageDates($primary, $secondary);

            $primary->save();

            // 2. Move activities, associations (deals included: Sales links them through
            //    associations), list memberships, property history, and lifecycle transitions
            $this->merger->moveCoreRecords($primary, $secondary);

            // 3. Let other packages move the records they own
            ContactsMerged::dispatch($primary, $secondary);

            // 4. Log audit note
            $primary->logActivity(
                type: ActivityType::Note,
                title: 'Contact Merged',
                body: "Merged duplicate contact {$secondary->email} (ID #{$secondary->id}) into this record."
            );

            // 5. Soft-delete secondary record
            $secondary->delete();

            return $primary->fresh() ?? $primary;
        });
    }
}
