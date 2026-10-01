<?php

declare(strict_types=1);

namespace Focal\Core\Support\Enrichment;

use Focal\Core\Actions\ExtractCorporateDomainAction;
use Focal\Core\Contracts\EnrichmentDriver;

class HeuristicEnrichmentDriver implements EnrichmentDriver
{
    public function __construct(
        protected ExtractCorporateDomainAction $extractDomain
    ) {}

    /**
     * Enrich company metadata using heuristic domain intelligence and favicon services.
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
    public function enrich(string $domain): ?array
    {
        $clean = preg_replace('#^https?://#', '', strtolower(trim($domain))) ?? '';
        $clean = preg_replace('#^www\.#', '', $clean) ?? '';
        $clean = explode('/', $clean)[0];

        if (empty($clean) || ! str_contains($clean, '.')) {
            return null;
        }

        $name = $this->extractDomain->deriveCompanyName($clean);
        $industry = $this->detectIndustry($clean);
        $techStack = $this->detectTechStack($clean);

        return [
            'name' => $name,
            'industry' => $industry,
            'employee_count_range' => '11-50',
            'logo_url' => "https://www.google.com/s2/favicons?domain={$clean}&sz=128",
            'tech_stack' => $techStack,
            'description' => "{$name} corporate domain profile and tech intelligence.",
        ];
    }

    /**
     * Infer primary industry vertical from domain tokens.
     */
    protected function detectIndustry(string $domain): string
    {
        $d = strtolower($domain);

        if (str_ends_with($d, '.ai') || str_contains($d, 'ai.') || str_contains($d, 'deep') || str_contains($d, 'neural')) {
            return 'Artificial Intelligence & Machine Learning';
        }

        if (str_contains($d, 'pay') || str_contains($d, 'fin') || str_contains($d, 'bank') || str_contains($d, 'crypto') || str_contains($d, 'capital')) {
            return 'Financial Services & FinTech';
        }

        if (str_contains($d, 'cloud') || str_contains($d, 'host') || str_contains($d, 'infra') || str_contains($d, 'ops') || str_contains($d, 'stack')) {
            return 'Cloud Infrastructure & DevOps';
        }

        if (str_contains($d, 'sec') || str_contains($d, 'guard') || str_contains($d, 'vault') || str_contains($d, 'auth') || str_contains($d, 'shield')) {
            return 'Cybersecurity & Identity';
        }

        if (str_contains($d, 'shop') || str_contains($d, 'store') || str_contains($d, 'cart') || str_contains($d, 'commerce') || str_contains($d, 'retail')) {
            return 'E-Commerce & Retail';
        }

        if (str_contains($d, 'health') || str_contains($d, 'med') || str_contains($d, 'care') || str_contains($d, 'bio') || str_contains($d, 'clinic')) {
            return 'Healthcare & Life Sciences';
        }

        return 'Software & Technology';
    }

    /**
     * Infer probable tech stack tools from domain indicators.
     *
     * @return list<string>
     */
    protected function detectTechStack(string $domain): array
    {
        $d = strtolower($domain);

        if (str_contains($d, 'ai') || str_contains($d, 'neural')) {
            return ['Python', 'OpenAI', 'PyTorch', 'AWS'];
        }

        if (str_contains($d, 'pay') || str_contains($d, 'fin')) {
            return ['Stripe', 'Plaid', 'PostgreSQL', 'Docker'];
        }

        if (str_contains($d, 'cloud') || str_contains($d, 'dev')) {
            return ['Kubernetes', 'Terraform', 'AWS', 'Go'];
        }

        return ['Laravel', 'PostgreSQL', 'TailwindCSS', 'Redis'];
    }
}
