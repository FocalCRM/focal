<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Core\Models\Company;
use Odden\Filament\Tests\Fixtures\User;
use Odden\Marketing\Models\MarketingAsset;
use Odden\Marketing\Models\MarketingEvent;
use Odden\Marketing\Models\NpsSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketingAdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_nps_survey_filament_page_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create();

        $survey = NpsSurvey::create([
            'name' => 'Executive NPS Assessment',
            'is_active' => true,
            'description' => 'Targeting C-suite accounts after 90 days',
        ]);

        $response = $this->actingAs($user)->get('/admin/nps-surveys');
        $response->assertStatus(200);
        $response->assertSee('Executive NPS Assessment');
    }

    public function test_filament_company_resource_displays_abm_tier(): void
    {
        $user = User::factory()->create();

        $company = Company::create([
            'name' => 'Datadog Europe',
            'domain' => 'datadog.com',
            'account_tier' => 'tier_1',
            'intent_score' => 85,
            'intent_surge' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/companies');
        $response->assertStatus(200);
        $response->assertSee('Datadog Europe');
        $response->assertSee('Tier 1');
    }

    public function test_filament_admin_resources_accessible(): void
    {
        $user = User::factory()->create();

        $asset = MarketingAsset::create([
            'name' => 'Odden Architecture Guide',
            'asset_type' => 'guide',
        ]);

        $event = MarketingEvent::create([
            'title' => 'DevOps Modernization Summit',
            'event_type' => 'in_person',
        ]);

        $this->actingAs($user)->get('/admin/marketing-assets')
            ->assertStatus(200)
            ->assertSee('Odden Architecture Guide');

        $this->actingAs($user)->get('/admin/marketing-events')
            ->assertStatus(200)
            ->assertSee('DevOps Modernization Summit');
    }
}
