<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use App\Models\User;
use Focal\Sales\Database\Seeders\SalesDatabaseSeeder;
use Focal\Sales\Enums\LeadRoutingStrategy;
use Focal\Sales\Models\Deal;
use Focal\Sales\Models\LeadRoutingRule;
use Focal\Sales\Models\Pipeline;
use Focal\Sales\Models\SalesMeetingLink;
use Focal\Sales\Models\SalesPlaybook;
use Focal\Sales\Models\SalesSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseSalesResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_sales_sequences_index(): void
    {
        $user = User::factory()->create();
        SalesSequence::create([
            'name' => 'High-Velocity Outbound',
            'steps' => [
                ['step' => 1, 'type' => 'automated_email', 'delay_days' => 0, 'title' => 'Intro Email'],
            ],
            'is_active' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/admin/sales-sequences');

        $response->assertSuccessful();
        $response->assertSee('High-Velocity Outbound');
    }

    public function test_authenticated_user_can_access_sales_playbooks_index(): void
    {
        $user = User::factory()->create();
        SalesPlaybook::create(array_merge(SalesPlaybook::defaultBantPreset(), [
            'user_id' => $user->id,
        ]));

        $response = $this->actingAs($user)->get('/admin/sales-playbooks');

        $response->assertSuccessful();
        $response->assertSee('BANT Qualification Playbook');
    }

    public function test_authenticated_user_can_access_meeting_links_index(): void
    {
        $user = User::factory()->create();
        SalesMeetingLink::create([
            'user_id' => $user->id,
            'title' => 'Demo Strategy Session',
            'slug' => 'demo-strategy-session',
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/sales-meeting-links');

        $response->assertSuccessful();
        $response->assertSee('Demo Strategy Session');
    }

    public function test_authenticated_user_can_access_lead_routing_rules_index(): void
    {
        $user = User::factory()->create();
        LeadRoutingRule::create([
            'name' => 'Round Robin All Inbound',
            'strategy' => LeadRoutingStrategy::RoundRobin,
            'assigned_user_ids' => [$user->id],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/lead-routing-rules');

        $response->assertSuccessful();
        $response->assertSee('Round Robin All Inbound');
    }

    public function test_sales_database_seeder_populates_enterprise_features(): void
    {
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);

        $this->seed(SalesDatabaseSeeder::class);

        $this->assertDatabaseHas('focal_sales_playbooks', ['slug' => 'bant-qualification']);
        $this->assertDatabaseHas('focal_sales_playbooks', ['slug' => 'meddic-enterprise']);
        $this->assertDatabaseHas('focal_sales_sequences', ['name' => 'Enterprise Outbound 14-Day Cadence']);
        $this->assertDatabaseHas('focal_sales_meeting_links', ['slug' => 'beth-caldwell']);
        $this->assertDatabaseHas('focal_sales_lead_routing_rules', ['name' => 'Inbound Enterprise Round Robin']);

        // Verify seeded deals with health scores
        $deals = Deal::where('pipeline_id', $pipeline->id)->get();
        $this->assertNotEmpty($deals);
        foreach ($deals as $deal) {
            $health = $deal->getHealthScore();
            $this->assertGreaterThanOrEqual(0, $health['score']);
            $this->assertLessThanOrEqual(100, $health['score']);
            $this->assertNotEmpty($health['badge_label']);
            $this->assertIsArray($health['factors']);
        }
    }
}
