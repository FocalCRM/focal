<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use Focal\Filament\Resources\DealResource\Pages\KanbanDeals;
use Focal\Filament\Tests\Fixtures\User;
use Focal\Sales\Enums\DealStatus;
use Focal\Sales\Models\Deal;
use Focal\Sales\Models\Pipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class DealResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_deals_index(): void
    {
        $user = User::factory()->create();
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);
        Deal::factory()->count(3)->create(['pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages->first()->id]);

        $response = $this->actingAs($user)->get('/admin/deals');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_deals_kanban_board(): void
    {
        $user = User::factory()->create();
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);
        $stage = $pipeline->stages->first();

        $deal = Deal::factory()->create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'name' => 'SpaceX Heavy Booster Expansion',
            'amount' => 500000.00,
        ]);

        $response = $this->actingAs($user)->get('/admin/deals/board');

        $response->assertSuccessful();
        $response->assertSee('Deals Pipeline Board');
        $response->assertSee('SpaceX Heavy Booster Expansion');
        $response->assertSee('$500,000.00');
    }

    public function test_kanban_board_can_move_deal_between_stages(): void
    {
        $user = User::factory()->create();
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);
        $stages = $pipeline->stages;
        $discovery = $stages[0];
        $negotiation = $stages[3];

        $deal = Deal::factory()->create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $discovery->id,
            'status' => DealStatus::Open,
        ]);

        Livewire::actingAs($user)
            ->test(KanbanDeals::class)
            ->call('moveDeal', $deal->id, $negotiation->id)
            ->assertSuccessful();

        $deal->refresh();
        $this->assertSame($negotiation->id, $deal->stage_id);
    }

    public function test_authenticated_user_can_access_view_deal_page(): void
    {
        $user = User::factory()->create();
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);
        $deal = Deal::factory()->create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $pipeline->stages->first()->id,
            'name' => 'Stripe Global Issuing Contract',
        ]);

        $response = $this->actingAs($user)->get("/admin/deals/{$deal->id}");

        $response->assertSuccessful();
        $response->assertSee('Stripe Global Issuing Contract');
    }
}
