<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Odden\Core\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FindDuplicateCompaniesAction
{
    /**
     * Find groups of duplicate companies based on domain or name.
     *
     * @return array<int, array{
     *     match_field: string,
     *     match_value: string,
     *     companies: Collection<int, Company>
     * }>
     */
    public function execute(): array
    {
        $duplicates = [];

        // 1. Duplicate Domains
        $duplicateDomains = Company::query()
            ->select('domain', DB::raw('count(*) as count'))
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain')
            ->havingRaw('count(*) > ?', [1])
            ->pluck('domain');

        foreach ($duplicateDomains as $domain) {
            $companies = Company::query()->where('domain', $domain)->orderBy('id')->get();
            if ($companies->count() > 1) {
                $duplicates[] = [
                    'match_field' => 'domain',
                    'match_value' => (string) $domain,
                    'companies' => $companies,
                ];
            }
        }

        // 2. Duplicate Names
        $duplicateNames = Company::query()
            ->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->havingRaw('count(*) > ?', [1])
            ->pluck('name');

        foreach ($duplicateNames as $name) {
            $companies = Company::query()->where('name', $name)->orderBy('id')->get();

            $ids = $companies->pluck('id')->sort()->values()->all();
            $alreadyMatched = false;
            foreach ($duplicates as $existing) {
                $existingIds = $existing['companies']->pluck('id')->sort()->values()->all();
                if ($ids === $existingIds) {
                    $alreadyMatched = true;
                    break;
                }
            }

            if (! $alreadyMatched && $companies->count() > 1) {
                $duplicates[] = [
                    'match_field' => 'name',
                    'match_value' => (string) $name,
                    'companies' => $companies,
                ];
            }
        }

        return $duplicates;
    }
}
