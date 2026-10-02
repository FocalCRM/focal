<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Core\Models\Contact;
use Odden\Core\Models\CrmList;
use Odden\Filament\Pages\MarketingCockpit;
use Odden\Filament\Pages\UtmLinkBuilder;
use Odden\Filament\Resources\MarketingTemplateResource;
use Odden\Filament\Tests\Fixtures\User;
use Odden\Marketing\Enums\CampaignStatus;
use Odden\Marketing\Enums\SubscriptionStatus;
use Odden\Marketing\Models\Campaign;
use Odden\Marketing\Models\MarketingForm;
use Odden\Marketing\Models\MarketingSubscription;
use Odden\Marketing\Models\MarketingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class MarketingFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_marketing_cockpit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/marketing-cockpit');

        $response->assertSuccessful();
        $response->assertSee('Marketing Command Center');
        $response->assertSee('Broadcasts Sent');
        $response->assertSee('Emails Delivered');
        $response->assertSee('Avg Open Rate');
        $response->assertSee('Click Rate (CTR)');
        $response->assertSee('Leads Captured');
        $response->assertSee('Recent Broadcast Campaigns');
    }

    public function test_authenticated_user_can_access_campaigns_resource(): void
    {
        $user = User::factory()->create();

        Campaign::create([
            'name' => 'Spring Product Launch',
            'subject' => 'Check out our new tools',
            'sender_name' => 'Odden',
            'sender_email' => 'news@odden.test',
            'status' => CampaignStatus::Draft,
        ]);

        $response = $this->actingAs($user)->get('/admin/campaigns');

        $response->assertSuccessful();
        $response->assertSee('Spring Product Launch');
    }

    public function test_authenticated_user_can_access_templates_resource(): void
    {
        $user = User::factory()->create();

        MarketingTemplate::create([
            'name' => 'Executive Digest Newsletter',
            'subject' => 'Weekly Brief',
            'body_html' => '<p>Digest content</p>',
            'category' => 'newsletter',
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-templates');

        $response->assertSuccessful();
        $response->assertSee('Executive Digest Newsletter');
    }

    public function test_authenticated_user_can_access_forms_resource(): void
    {
        $user = User::factory()->create();

        MarketingForm::create([
            'title' => 'Request Developer Sandbox Access',
            'slug' => 'developer-sandbox',
            'fields_schema' => [
                ['name' => 'email', 'type' => 'email', 'required' => true],
            ],
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-forms');

        $response->assertSuccessful();
        $response->assertSee('Request Developer Sandbox Access');
    }

    public function test_marketing_cockpit_can_send_campaign_now(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => 'Ada',
            'email' => 'ada@lovelace.org',
        ]);
        $list = CrmList::create(['name' => 'Newsletter', 'type' => 'static']);
        $list->addMember($contact);

        $campaign = Campaign::create([
            'name' => 'Immediate Blast',
            'subject' => 'Live now',
            'sender_name' => 'Odden Team',
            'sender_email' => 'news@odden.test',
            'list_id' => $list->id,
            'status' => CampaignStatus::Draft,
        ]);

        Livewire::actingAs($user)
            ->test(MarketingCockpit::class)
            ->call('sendCampaignNow', $campaign->id)
            ->assertSuccessful();

        $campaign->refresh();
        $this->assertSame(CampaignStatus::Sent, $campaign->status);
        $this->assertSame(1, $campaign->delivered_count);
    }

    public function test_marketing_cockpit_refuses_to_send_a_campaign_without_an_audience(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create(['email' => 'ada@lovelace.org']);

        $campaign = Campaign::create([
            'name' => 'No Audience',
            'subject' => 'Live now',
            'sender_name' => 'Odden Team',
            'sender_email' => 'news@odden.test',
            'status' => CampaignStatus::Draft,
        ]);

        Livewire::actingAs($user)
            ->test(MarketingCockpit::class)
            ->call('sendCampaignNow', $campaign->id)
            ->assertSuccessful()
            ->assertNotified('Campaign Has No Audience');

        $this->assertSame(CampaignStatus::Draft, $campaign->fresh()?->status);
        $this->assertSame(0, $campaign->recipients()->count());
    }

    public function test_authenticated_user_can_access_marketing_attribution_dashboard(): void
    {
        $user = User::factory()->create();

        Campaign::create([
            'name' => 'Paid Search Q4',
            'subject' => 'Find more pipeline',
            'sender_name' => 'Odden',
            'sender_email' => 'ads@odden.test',
            'status' => CampaignStatus::Sent,
            'budget' => 3000.00,
            'actual_cost' => 2500.00,
            'topic' => 'product_updates',
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-attribution');

        $response->assertSuccessful();
        $response->assertSee('Multi-Touch Attribution & Campaign ROI');
        $response->assertSee('Allocated Budget');
        $response->assertSee('Actual Direct Spend');
        $response->assertSee('Paid Search Q4');
    }

    public function test_authenticated_user_can_access_utm_link_builder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/utm-link-builder');

        $response->assertSuccessful();
        $response->assertSee('Inbound Campaign UTM Builder');
        $response->assertSee('Generated Inbound Tracking URL');

        Livewire::actingAs($user)
            ->test(UtmLinkBuilder::class)
            ->set('baseUrl', 'https://odden.test/landing')
            ->set('customCampaign', 'summer-blast')
            ->set('utmSource', 'linkedin')
            ->set('utmMedium', 'cpc')
            ->assertSee('https://odden.test/landing?utm_source=linkedin&utm_medium=cpc&utm_campaign=summer-blast');
    }

    public function test_authenticated_user_can_manage_marketing_suppression_list(): void
    {
        $user = User::factory()->create();

        $subscription = MarketingSubscription::create([
            'email' => 'optout@example.com',
            'status' => SubscriptionStatus::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-subscriptions');

        $response->assertSuccessful();
        $response->assertSee('optout@example.com');
    }

    public function test_marketing_template_renders_live_preview_html(): void
    {
        $template = MarketingTemplate::create([
            'name' => 'Feature Spotlight',
            'subject' => 'Hi {{contact.first_name}}!',
            'body_html' => '<h1>Welcome {{contact.first_name}} from {{company.name}}</h1>',
            'category' => 'product_update',
        ]);

        $rendered = MarketingTemplateResource::renderSampleHtml($template);

        $this->assertStringContainsString('Alex', $rendered);
        $this->assertStringContainsString('Acme Corporation', $rendered);
    }

    public function test_authenticated_user_can_access_marketing_calendar(): void
    {
        $user = User::factory()->create();

        Campaign::create([
            'name' => 'Monthly Product Blast',
            'subject' => 'Live now',
            'sender_name' => 'Odden Team',
            'sender_email' => 'news@odden.test',
            'status' => CampaignStatus::Scheduled,
            'scheduled_at' => now()->startOfMonth()->addDays(5),
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-calendar');

        $response->assertSuccessful();
        $response->assertSee('Marketing Campaign Calendar');
        $response->assertSee('Monthly Product Blast');
    }

    public function test_authenticated_user_can_access_campaign_benchmarking(): void
    {
        $user = User::factory()->create();

        $c1 = Campaign::create([
            'name' => 'Product Announce A',
            'subject' => 'Announce A',
            'sender_name' => 'Odden',
            'sender_email' => 'team@odden.test',
            'status' => CampaignStatus::Sent,
            'delivered_count' => 100,
            'unique_opens_count' => 35,
            'unique_clicks_count' => 12,
        ]);

        $response = $this->actingAs($user)->get('/admin/campaign-benchmarking');

        $response->assertSuccessful();
        $response->assertSee('Multi-Campaign Benchmarking');
        $response->assertSee('Product Announce A');
        $response->assertSee('Cohort Avg Open Rate');
    }

    public function test_authenticated_user_can_access_sender_domain_health(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/sender-domain-health');

        $response->assertSuccessful();
        $response->assertSee('Email Authentication');
        $response->assertSee('Deliverability Health');
        $response->assertSee('SPF (Sender Policy Framework)');
        $response->assertSee('DMARC Alignment');
        $response->assertSee('DKIM Signature Key');
    }

    public function test_authenticated_user_can_access_marketing_suppressions(): void
    {
        $user = User::factory()->create();

        MarketingSubscription::create([
            'email' => 'blocked-contact@domain.com',
            'status' => SubscriptionStatus::Bounced,
            'unsubscribed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/marketing-subscriptions');

        $response->assertSuccessful();
        $response->assertSee('Suppression List');
        $response->assertSee('blocked-contact@domain.com');
    }
}
