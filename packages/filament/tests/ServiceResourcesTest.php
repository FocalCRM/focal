<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use App\Models\User;
use Focal\Filament\Resources\TicketResource\Pages\KanbanTickets;
use Focal\Filament\Resources\TicketResource\Pages\ListTickets;
use Focal\Service\Database\Seeders\ServiceDatabaseSeeder;
use Focal\Service\Enums\TicketPriority;
use Focal\Service\Enums\TicketSource;
use Focal\Service\Enums\TicketStatus;
use Focal\Service\Models\CannedResponse;
use Focal\Service\Models\KnowledgeArticle;
use Focal\Service\Models\SlaPolicy;
use Focal\Service\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_tickets_index(): void
    {
        $user = User::factory()->create();
        Ticket::create([
            'ticket_number' => 'TICK-2026-TEST1',
            'subject' => 'Cannot connect to production database',
            'status' => TicketStatus::New,
            'priority' => TicketPriority::Urgent,
            'source' => TicketSource::Api,
            'owner_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/admin/tickets');

        $response->assertSuccessful();
        $response->assertSee('Cannot connect to production database');
        $response->assertSee('TICK-2026-TEST1');
    }

    public function test_authenticated_user_can_access_sla_policies_index(): void
    {
        $user = User::factory()->create();
        SlaPolicy::create(SlaPolicy::defaultPreset());

        $response = $this->actingAs($user)->get('/admin/sla-policies');

        $response->assertSuccessful();
        $response->assertSee('Standard Customer Support SLA');
    }

    public function test_authenticated_user_can_access_knowledge_articles_index(): void
    {
        $user = User::factory()->create();
        KnowledgeArticle::create([
            'title' => 'Configuring Okta SAML 2.0 Single Sign-On',
            'slug' => 'configuring-okta-saml-sso',
            'category' => 'Authentication',
            'body' => 'Step by step instructions for Okta integration.',
            'is_published' => true,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/admin/knowledge-articles');

        $response->assertSuccessful();
        $response->assertSee('Configuring Okta SAML 2.0 Single Sign-On');
    }

    public function test_authenticated_user_can_access_canned_responses_index(): void
    {
        $user = User::factory()->create();
        CannedResponse::create([
            'title' => 'Request System Logs',
            'shortcut' => '!logs',
            'category' => 'Diagnostics',
            'content' => 'Please provide the storage/logs/laravel.log file.',
            'user_id' => $user->id,
            'is_shared' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/canned-responses');

        $response->assertSuccessful();
        $response->assertSee('!logs');
        $response->assertSee('Request System Logs');
    }

    public function test_authenticated_user_can_access_tickets_kanban_board(): void
    {
        $user = User::factory()->create();
        Ticket::create([
            'ticket_number' => 'TICK-BOARD-01',
            'subject' => 'Stuck during Okta Auth loop',
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::High,
            'source' => TicketSource::Email,
            'owner_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/admin/tickets/board');

        $response->assertSuccessful();
        $response->assertSee('Support Tickets Board');
        $response->assertSee('TICK-BOARD-01');
        $response->assertSee('Stuck during Okta Auth loop');
    }

    public function test_kanban_board_can_move_ticket_between_statuses(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::create([
            'ticket_number' => 'TICK-MOVE-01',
            'subject' => 'Investigate network latency',
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Medium,
        ]);

        Livewire::actingAs($user)
            ->test(KanbanTickets::class)
            ->call('moveTicket', $ticket->id, 'resolved')
            ->assertSuccessful();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Resolved, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_service_database_seeder_populates_help_desk_data(): void
    {
        $this->seed(ServiceDatabaseSeeder::class);

        $this->assertDatabaseHas('focal_service_sla_policies', ['name' => 'Standard Customer Support SLA']);
        $this->assertDatabaseHas('focal_service_sla_policies', ['name' => 'Enterprise 24/7 Mission-Critical SLA']);
        $this->assertDatabaseHas('focal_service_articles', ['slug' => 'configuring-saml-sso']);
        $this->assertDatabaseHas('focal_service_canned_responses', ['shortcut' => '!moreinfo']);
        $this->assertDatabaseHas('focal_service_tickets', ['ticket_number' => 'TICK-2026-0001']);
        $this->assertDatabaseHas('focal_service_tickets', ['ticket_number' => 'TICK-2026-0004', 'csat_rating' => 5]);

        // Verify conversation messages seeded on Ticket 2
        $ticket2 = Ticket::where('ticket_number', 'TICK-2026-0002')->first();
        $this->assertNotNull($ticket2);
        $this->assertGreaterThanOrEqual(3, $ticket2->messages()->count());
    }

    public function test_ticket_resource_table_can_merge_tickets(): void
    {
        $user = User::factory()->create();

        $primary = Ticket::create([
            'ticket_number' => 'TICK-PRI-01',
            'subject' => 'Primary production issue',
            'status' => TicketStatus::Open,
        ]);

        $secondary = Ticket::create([
            'ticket_number' => 'TICK-SEC-02',
            'subject' => 'Duplicate production issue',
            'status' => TicketStatus::New,
        ]);

        Livewire::actingAs($user)
            ->test(ListTickets::class)
            ->callTableAction('mergeTicket', $secondary, data: [
                'primary_ticket_id' => $primary->id,
                'merge_reason' => 'Duplicate alert from monitoring system.',
            ])
            ->assertHasNoTableActionErrors();

        $secondary->refresh();
        $this->assertSame(TicketStatus::Closed, $secondary->status);
        $this->assertSame($primary->id, $secondary->merged_into_ticket_id);
    }
}
