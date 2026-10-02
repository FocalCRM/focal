<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Filament\Resources\SalesMeetingLinkResource\Pages\CreateSalesMeetingLink;
use Odden\Filament\Resources\SalesMeetingLinkResource\Pages\EditSalesMeetingLink;
use Odden\Filament\Tests\Fixtures\User;
use Odden\Sales\Database\Seeders\SalesDatabaseSeeder;
use Odden\Sales\Enums\LeadRoutingStrategy;
use Odden\Sales\Models\Deal;
use Odden\Sales\Models\LeadRoutingRule;
use Odden\Sales\Models\Pipeline;
use Odden\Sales\Models\SalesMeetingLink;
use Odden\Sales\Models\SalesPlaybook;
use Odden\Sales\Models\SalesSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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

    public function test_meeting_link_form_saves_working_hours_buffer_and_timezone(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateSalesMeetingLink::class)
            ->fillForm([
                'user_id' => $user->id,
                'title' => 'Discovery Call',
                'slug' => 'discovery-call',
                'duration_minutes' => 30,
                'buffer_minutes' => 15,
                'timezone' => 'Europe/London',
                'working_hours' => [
                    'monday' => ['09:00-12:00', '13:00-17:00'],
                    'wednesday' => [' 10:00-16:00 '],
                    'friday' => [],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $link = SalesMeetingLink::query()->where('slug', 'discovery-call')->firstOrFail();

        $this->assertSame(15, $link->buffer_minutes);
        $this->assertSame('Europe/London', $link->timezone);
        $this->assertSame([
            'monday' => ['09:00-12:00', '13:00-17:00'],
            'wednesday' => ['10:00-16:00'],
        ], $link->working_hours);

        Livewire::actingAs($user)
            ->test(EditSalesMeetingLink::class, ['record' => $link->getRouteKey()])
            ->assertFormSet([
                'buffer_minutes' => 15,
                'timezone' => 'Europe/London',
                'working_hours.monday' => ['09:00-12:00', '13:00-17:00'],
            ])
            ->fillForm([
                'buffer_minutes' => 0,
                'timezone' => null,
                'working_hours' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], []),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $link->refresh();

        $this->assertSame(0, $link->buffer_minutes);
        $this->assertNull($link->timezone);
        $this->assertNull($link->working_hours);
    }

    public function test_meeting_link_form_rejects_malformed_working_hours_and_unknown_timezones(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateSalesMeetingLink::class)
            ->fillForm([
                'user_id' => $user->id,
                'title' => 'Discovery Call',
                'slug' => 'discovery-call',
                'duration_minutes' => 30,
                'buffer_minutes' => -5,
                'timezone' => 'Mars/Olympus_Mons',
                'working_hours' => [
                    'monday' => ['9am-5pm'],
                    'tuesday' => ['17:00-09:00'],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'buffer_minutes',
                'timezone',
                'working_hours.monday.0',
                'working_hours.tuesday.0',
            ]);

        $this->assertFalse(SalesMeetingLink::query()->where('slug', 'discovery-call')->exists());
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

        $this->assertDatabaseHas('odden_sales_playbooks', ['slug' => 'bant-qualification']);
        $this->assertDatabaseHas('odden_sales_playbooks', ['slug' => 'meddic-enterprise']);
        $this->assertDatabaseHas('odden_sales_sequences', ['name' => 'Enterprise Outbound 14-Day Cadence']);
        $this->assertDatabaseHas('odden_sales_meeting_links', ['slug' => 'beth-caldwell']);
        $this->assertDatabaseHas('odden_sales_lead_routing_rules', ['name' => 'Inbound Enterprise Round Robin']);

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
