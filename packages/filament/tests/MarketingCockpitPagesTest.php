<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use Focal\Core\Models\Company;
use Focal\Filament\Pages\AbmCockpit;
use Focal\Filament\Resources\CampaignResource;
use Focal\Filament\Tests\Fixtures\User;
use Focal\Marketing\Actions\CalculateCompanyIntentScoreAction;
use Focal\Marketing\Models\Campaign;
use Focal\Marketing\Models\MarketingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketingCockpitPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_abm_cockpit_page_metrics_and_actions(): void
    {
        $this->actingAs(User::factory()->create());

        $tier1 = Company::create([
            'name' => 'Snowflake Computing',
            'domain' => 'snowflake.com',
            'account_tier' => 'tier_1',
            'intent_score' => 90,
            'intent_surge' => true,
            'buying_committee_size' => 3,
        ]);

        $tier2 = Company::create([
            'name' => 'Datadog Inc',
            'domain' => 'datadoghq.com',
            'account_tier' => 'tier_2',
            'intent_score' => 40,
            'intent_surge' => false,
            'buying_committee_size' => 1,
        ]);

        Company::create([
            'name' => 'Small Startup',
            'domain' => 'startup.test',
            'account_tier' => 'tier_3',
            'intent_score' => 10,
            'intent_surge' => false,
        ]);

        $cockpit = new AbmCockpit;

        $this->assertSame(2, $cockpit->totalTargetAccounts);
        $this->assertSame(1, $cockpit->surgingAccountsCount);
        $this->assertSame(1, $cockpit->tier1AccountsCount);
        $this->assertSame(1, $cockpit->tier2AccountsCount);
        $this->assertSame(65.0, $cockpit->averageIntentScore);
        $this->assertSame(4, $cockpit->totalBuyingCommittee);

        // Test accounts filtering by tier
        $cockpit->setTier('surging');
        $this->assertCount(1, $cockpit->accounts);
        $this->assertSame('Snowflake Computing', $cockpit->accounts->first()?->name);

        $cockpit->setTier('all');
        $this->assertCount(2, $cockpit->accounts);

        // Test recalculateCompany action
        $action = new CalculateCompanyIntentScoreAction;
        $cockpit->recalculateCompany($tier1->id, $action);
        $this->assertNotNull($tier1->fresh()?->last_intent_activity_at);

        // Test recalculateAll
        $cockpit->recalculateAll($action);
        $this->assertDatabaseHas('focal_companies', [
            'id' => $tier1->id,
            'account_tier' => 'tier_1',
        ]);
    }

    public function test_campaign_and_template_device_mode_preview(): void
    {
        $template = MarketingTemplate::create([
            'name' => 'Product Announcement',
            'subject' => 'Major Update: Focal 2.0 Released',
            'preview_text' => 'Discover our new Account-Based Marketing cockpit.',
            'body_html' => '<h1>Hello {{contact.first_name}}</h1><p>We are thrilled to unveil Focal 2.0 for {{company.name}}.</p>',
        ]);

        $campaign = Campaign::create([
            'name' => 'Q4 Product Launch Broadcast',
            'subject' => 'Major Update: Focal 2.0 Released',
            'preview_text' => 'Discover our new Account-Based Marketing cockpit.',
            'sender_name' => 'Focal Marketing',
            'sender_email' => 'marketing@focal.test',
            'template_id' => $template->id,
        ]);

        // Test sample rendering helper
        $html = CampaignResource::renderSampleHtml($campaign);
        $this->assertStringContainsString('Hello Alex', $html);
        $this->assertStringContainsString('Acme Corporation', $html);

        // Test rendering the preview view
        $view = view('focal-marketing::template-preview', [
            'renderedHtml' => $html,
            'template' => $campaign,
        ])->render();

        $this->assertStringContainsString('Desktop (600px)', $view);
        $this->assertStringContainsString('Mobile Device (375px)', $view);
        $this->assertStringContainsString('Major Update: Focal 2.0 Released', $view);
        $this->assertStringContainsString('Alex Morgan', $view);
    }
}
