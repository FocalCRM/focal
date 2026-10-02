<?php

declare(strict_types=1);

namespace Odden\Core\Contracts;

interface EnrichmentDriver
{
    /**
     * Enrich company intelligence from corporate domain.
     *
     * @return array{
     *     name?: string,
     *     industry?: string,
     *     employee_count_range?: string,
     *     logo_url?: string,
     *     tech_stack?: list<string>,
     *     description?: string,
     *     city?: string,
     *     country?: string,
     *     linkedin_url?: string
     * }|null
     */
    public function enrich(string $domain): ?array;
}
