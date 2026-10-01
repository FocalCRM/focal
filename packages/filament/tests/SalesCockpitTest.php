<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use App\Models\User;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Enums\LeadStatus;
use Focal\Core\Models\Contact;
use Focal\Filament\Pages\SalesCockpit;
use Focal\Sales\Models\SalesSequence;
use Focal\Sales\Models\SalesSequenceEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesCockpitTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_sales_cockpit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/sales-cockpit');

        $response->assertSuccessful();
        $response->assertSee('Sales Prospecting Workspace');
        $response->assertSee('Your tasks');
        $response->assertSee('Your sequence activities');
        $response->assertSee('Guided actions');
    }

    public function test_sales_cockpit_can_advance_sequence_enrollment(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => 'Gordon',
            'last_name' => 'Freeman',
            'email' => 'gordon@blackmesa.com',
            'lead_status' => LeadStatus::New,
        ]);

        $sequence = SalesSequence::query()->create([
            'name' => 'Strategic Outreach',
            'is_active' => true,
            'steps' => [
                ['step' => 1, 'type' => 'email', 'delay_days' => 0, 'title' => 'Initial pitch'],
                ['step' => 2, 'type' => 'call', 'delay_days' => 2, 'title' => 'Follow up'],
            ],
        ]);

        $enrollment = SalesSequenceEnrollment::query()->create([
            'sequence_id' => $sequence->id,
            'contact_id' => $contact->id,
            'current_step' => 1,
            'status' => 'active',
            'next_step_due_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(SalesCockpit::class)
            ->call('advanceEnrollment', $enrollment->id)
            ->assertSuccessful();

        $enrollment->refresh();
        $this->assertSame(2, $enrollment->current_step);

        $contact->refresh();
        $this->assertSame(LeadStatus::InProgress, $contact->lead_status);
        $this->assertNotNull($contact->last_contacted_at);
    }

    public function test_sales_cockpit_can_log_quick_touch(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => 'Alyx',
            'last_name' => 'Vance',
            'email' => 'alyx@city17.org',
            'lead_status' => LeadStatus::New,
            'last_contacted_at' => null,
        ]);

        Livewire::actingAs($user)
            ->test(SalesCockpit::class)
            ->call('logQuickTouch', $contact->id, 'call')
            ->assertSuccessful();

        $contact->refresh();
        $this->assertSame(LeadStatus::AttemptedContact, $contact->lead_status);
        $this->assertNotNull($contact->last_contacted_at);

        $this->assertDatabaseHas('focal_activities', [
            'subject_type' => $contact->getMorphClass(),
            'subject_id' => $contact->id,
            'type' => ActivityType::Call->value,
        ]);
    }
}
