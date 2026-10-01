<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FindDuplicateContactsAction
{
    /**
     * Find groups of duplicate contacts based on matching email, phone, or full name.
     *
     * @return array<int, array{
     *     match_field: string,
     *     match_value: string,
     *     contacts: Collection<int, Contact>
     * }>
     */
    public function execute(): array
    {
        $duplicates = [];

        // 1. Duplicate Emails (case-insensitive)
        $duplicateEmails = Contact::query()
            ->select('email', DB::raw('count(*) as count'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy('email')
            ->havingRaw('count(*) > ?', [1])
            ->pluck('email');

        foreach ($duplicateEmails as $email) {
            $contacts = Contact::query()->where('email', $email)->orderBy('id')->get();
            if ($contacts->count() > 1) {
                $duplicates[] = [
                    'match_field' => 'email',
                    'match_value' => (string) $email,
                    'contacts' => $contacts,
                ];
            }
        }

        // 2. Duplicate Phones (ignoring non-digit characters)
        $duplicatePhones = Contact::query()
            ->select('phone', DB::raw('count(*) as count'))
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('count(*) > ?', [1])
            ->pluck('phone');

        foreach ($duplicatePhones as $phone) {
            $contacts = Contact::query()->where('phone', $phone)->orderBy('id')->get();
            // Don't duplicate if already matched by email
            $ids = $contacts->pluck('id')->sort()->values()->all();
            $alreadyMatched = false;
            foreach ($duplicates as $existing) {
                $existingIds = $existing['contacts']->pluck('id')->sort()->values()->all();
                if ($ids === $existingIds) {
                    $alreadyMatched = true;
                    break;
                }
            }

            if (! $alreadyMatched && $contacts->count() > 1) {
                $duplicates[] = [
                    'match_field' => 'phone',
                    'match_value' => (string) $phone,
                    'contacts' => $contacts,
                ];
            }
        }

        return $duplicates;
    }
}
