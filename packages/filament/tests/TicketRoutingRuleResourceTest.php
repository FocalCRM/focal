<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Filament\Tests\Fixtures\User;
use Odden\Service\Models\TicketRoutingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketRoutingRuleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_ticket_routing_rules_index(): void
    {
        $user = User::factory()->create();

        TicketRoutingRule::create([
            'name' => 'Urgent Auto-Assign Rule',
            'assigned_user_ids' => [$user->id],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/ticket-routing-rules');

        $response->assertSuccessful();
        $response->assertSee('Urgent Auto-Assign Rule');
        $response->assertSee('1 agents');
    }
}
