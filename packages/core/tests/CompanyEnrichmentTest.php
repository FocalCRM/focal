<?php

declare(strict_types=1);

namespace Focal\Core\Tests;

use Focal\Core\Actions\CreateCompanyAction;
use Focal\Core\Actions\EnrichCompanyAction;
use Focal\Core\Contracts\EnrichmentDriver;
use Focal\Core\Events\CompanyEnriched;
use Focal\Core\Models\Company;
use Focal\Core\Support\Enrichment\EnrichmentManager;
use Focal\Core\Support\Enrichment\HeuristicEnrichmentDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CompanyEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_heuristic_driver_extracts_enrichment_data(): void
    {
        /** @var HeuristicEnrichmentDriver $driver */
        $driver = app(HeuristicEnrichmentDriver::class);

        $aiData = $driver->enrich('anthropic.ai');
        $this->assertNotNull($aiData);
        $this->assertSame('Artificial Intelligence & Machine Learning', $aiData['industry']);
        $this->assertContains('Python', $aiData['tech_stack']);
        $this->assertStringContainsString('anthropic.ai', $aiData['logo_url']);

        $fintechData = $driver->enrich('squarepay.com');
        $this->assertNotNull($fintechData);
        $this->assertSame('Financial Services & FinTech', $fintechData['industry']);
        $this->assertContains('Stripe', $fintechData['tech_stack']);

        $invalidData = $driver->enrich('invalid-no-tld');
        $this->assertNull($invalidData);
    }

    public function test_enrich_company_action_enriches_company_and_dispatches_event(): void
    {
        Event::fake([CompanyEnriched::class]);

        $company = Company::factory()->create([
            'domain' => 'deepmind.ai',
            'industry' => null,
            'properties' => ['custom_notes' => 'Existing note'],
        ]);

        /** @var EnrichCompanyAction $action */
        $action = app(EnrichCompanyAction::class);
        $enriched = $action->execute($company);

        $this->assertSame('Artificial Intelligence & Machine Learning', $enriched->industry);
        $this->assertSame('Existing note', $enriched->getProperty('custom_notes'));
        $this->assertNotNull($enriched->getProperty('logo_url'));
        $this->assertNotNull($enriched->getProperty('tech_stack'));
        $this->assertNotNull($enriched->getProperty('enriched_at'));

        Event::assertDispatched(CompanyEnriched::class, function (CompanyEnriched $event) use ($company): bool {
            return $event->company->id === $company->id;
        });
    }

    public function test_can_register_and_use_custom_enrichment_driver(): void
    {
        /** @var EnrichmentManager $manager */
        $manager = app(EnrichmentManager::class);

        $customDriver = new class implements EnrichmentDriver
        {
            public function enrich(string $domain): ?array
            {
                return [
                    'industry' => 'Custom Aerospace',
                    'tech_stack' => ['Rust', 'WebAssembly'],
                    'logo_url' => 'https://example.com/custom-logo.png',
                ];
            }
        };

        $manager->extend('custom_driver', $customDriver);

        $company = Company::factory()->create([
            'domain' => 'spacex.com',
            'industry' => null,
        ]);

        /** @var EnrichCompanyAction $action */
        $action = app(EnrichCompanyAction::class);
        $action->execute($company, 'custom_driver');

        $this->assertSame('Custom Aerospace', $company->fresh()->industry);
        $this->assertSame(['Rust', 'WebAssembly'], $company->fresh()->getProperty('tech_stack'));
        $this->assertSame('https://example.com/custom-logo.png', $company->fresh()->getProperty('logo_url'));
    }

    public function test_create_company_action_with_enrich_flag_auto_enriches(): void
    {
        Event::fake([CompanyEnriched::class]);

        /** @var CreateCompanyAction $action */
        $action = app(CreateCompanyAction::class);

        $company = $action->execute([
            'name' => 'Scale AI',
            'domain' => 'scale.ai',
        ], enrich: true);

        $this->assertSame('scale.ai', $company->domain);
        $this->assertSame('Artificial Intelligence & Machine Learning', $company->industry);
        $this->assertNotNull($company->getProperty('logo_url'));

        Event::assertDispatched(CompanyEnriched::class);
    }
}
