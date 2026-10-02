<?php

declare(strict_types=1);

namespace Odden\Core\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Moves the records Core owns from a duplicate onto the record it is merged into.
 *
 * Used by MergeContactsAction and MergeCompaniesAction inside their transaction. Data owned by
 * other packages is moved by their listeners for ContactsMerged and CompaniesMerged.
 */
final class RecordMerger
{
    /**
     * Lifecycle stage date columns on contacts and companies. The earliest date is kept.
     */
    private const array STAGE_DATE_COLUMNS = [
        'became_subscriber_at',
        'became_lead_at',
        'became_marketing_qualified_lead_at',
        'became_sales_qualified_lead_at',
        'became_opportunity_at',
        'became_customer_at',
        'became_evangelist_at',
        'became_other_at',
    ];

    /**
     * Set each lifecycle stage date on the primary to the earlier of the two records' dates.
     * Only changes attributes; the caller saves the primary.
     */
    public function keepEarliestStageDates(Model $primary, Model $secondary): void
    {
        foreach (self::STAGE_DATE_COLUMNS as $column) {
            $secondaryDate = $secondary->getAttribute($column);
            if ($secondaryDate === null) {
                continue;
            }

            $primaryDate = $primary->getAttribute($column);
            if ($primaryDate === null || $secondaryDate < $primaryDate) {
                $primary->setAttribute($column, $secondaryDate);
            }
        }
    }

    /**
     * Move activities, associations, list memberships, property history, and lifecycle stage
     * transitions from $secondary to $primary.
     */
    public function moveCoreRecords(Model $primary, Model $secondary): void
    {
        $this->moveMorphOwner(config('odden-core.tables.activities', 'odden_activities'), 'subject', $primary, $secondary);
        $this->moveMorphOwner(config('odden-core.tables.property_history', 'odden_property_history'), 'auditable', $primary, $secondary);
        $this->moveMorphOwner(config('odden-core.tables.lifecycle_stage_transitions', 'odden_lifecycle_stage_transitions'), 'record', $primary, $secondary);
        $this->moveListMemberships($primary, $secondary);
        $this->moveAssociations($primary, $secondary);
    }

    private function moveMorphOwner(string $table, string $morph, Model $primary, Model $secondary): void
    {
        DB::table($table)
            ->where("{$morph}_type", $secondary->getMorphClass())
            ->where("{$morph}_id", $secondary->getKey())
            ->update(["{$morph}_type" => $primary->getMorphClass(), "{$morph}_id" => $primary->getKey()]);
    }

    private function moveListMemberships(Model $primary, Model $secondary): void
    {
        $table = config('odden-core.tables.list_memberships', 'odden_list_memberships');

        $primaryListIds = DB::table($table)
            ->where('member_type', $primary->getMorphClass())
            ->where('member_id', $primary->getKey())
            ->pluck('list_id');

        $secondaryMemberships = DB::table($table)
            ->where('member_type', $secondary->getMorphClass())
            ->where('member_id', $secondary->getKey());

        // The primary is already on these lists: a second row would break the unique index.
        (clone $secondaryMemberships)->whereIn('list_id', $primaryListIds)->delete();

        $secondaryMemberships->update(['member_type' => $primary->getMorphClass(), 'member_id' => $primary->getKey()]);
    }

    private function moveAssociations(Model $primary, Model $secondary): void
    {
        $table = config('odden-core.tables.associations', 'odden_associations');
        $primaryType = $primary->getMorphClass();
        $primaryId = $primary->getKey();

        foreach (['parent' => 'child', 'child' => 'parent'] as $side => $otherSide) {
            $associations = DB::table($table)
                ->where("{$side}_type", $secondary->getMorphClass())
                ->where("{$side}_id", $secondary->getKey())
                ->get();

            foreach ($associations as $association) {
                $otherType = $association->{"{$otherSide}_type"};
                $otherId = $association->{"{$otherSide}_id"};

                // A link between the two merged records would become a link from the primary to itself.
                $isSelfLink = ($otherType === $primaryType && (string) $otherId === (string) $primaryId)
                    || ($otherType === $secondary->getMorphClass() && (string) $otherId === (string) $secondary->getKey());

                $isDuplicate = DB::table($table)
                    ->where("{$side}_type", $primaryType)
                    ->where("{$side}_id", $primaryId)
                    ->where("{$otherSide}_type", $otherType)
                    ->where("{$otherSide}_id", $otherId)
                    ->where('type', $association->type)
                    ->exists();

                if ($isSelfLink || $isDuplicate) {
                    DB::table($table)->where('id', $association->id)->delete();

                    continue;
                }

                DB::table($table)
                    ->where('id', $association->id)
                    ->update(["{$side}_type" => $primaryType, "{$side}_id" => $primaryId]);
            }
        }
    }
}
