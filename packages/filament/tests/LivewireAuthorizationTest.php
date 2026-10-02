<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Core\Enums\ActivityStatus;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Enums\LeadStatus;
use Odden\Core\Models\Activity;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;
use Odden\Filament\Pages\AbmCockpit;
use Odden\Filament\Pages\DataQuality;
use Odden\Filament\Pages\MarketingCockpit;
use Odden\Filament\Pages\SalesCockpit;
use Odden\Filament\Pages\ServiceCockpit;
use Odden\Filament\Resources\DealResource\Pages\KanbanDeals;
use Odden\Filament\Resources\TicketResource\Pages\KanbanTickets;
use Odden\Filament\Tests\Fixtures\User;
use Odden\Marketing\Enums\CampaignStatus;
use Odden\Marketing\Models\Campaign;
use Odden\Sales\Enums\DealStatus;
use Odden\Sales\Models\Deal;
use Odden\Sales\Models\Pipeline;
use Odden\Sales\Models\SalesSequence;
use Odden\Sales\Models\SalesSequenceEnrollment;
use Odden\Service\Enums\TicketPriority;
use Odden\Service\Enums\TicketStatus;
use Odden\Service\Models\CannedResponse;
use Odden\Service\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class LivewireAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function ticket(array $attributes = []): Ticket
    {
        return Ticket::create(array_merge([
            'subject' => 'Printer on fire',
            'status' => TicketStatus::New,
            'priority' => TicketPriority::High,
            'owner_id' => null,
        ], $attributes));
    }

    public function test_claim_ticket_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Ticket::class], ['update']);
        $ticket = $this->ticket();

        Livewire::actingAs(User::factory()->create())
            ->test(ServiceCockpit::class)
            ->call('claimTicket', $ticket->id)
            ->assertForbidden();

        $this->assertNull($ticket->fresh()?->owner_id);
    }

    public function test_quick_reply_is_forbidden_when_policy_denies_update(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket(['status' => TicketStatus::Open, 'owner_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(ServiceCockpit::class)
            ->call('openReplyModal', $ticket->id)
            ->set('replyBody', 'Have you tried turning it off and on again?');

        $this->denyAbilities([Ticket::class], ['update']);

        $component->call('sendQuickReply')->assertForbidden();

        $this->assertCount(0, $ticket->fresh()?->messages ?? []);
    }

    public function test_reply_and_resolve_modals_do_not_open_when_policy_denies_update(): void
    {
        $this->denyAbilities([Ticket::class], ['update']);
        $ticket = $this->ticket(['status' => TicketStatus::Open]);

        Livewire::actingAs(User::factory()->create())
            ->test(ServiceCockpit::class)
            ->call('openReplyModal', $ticket->id)
            ->assertForbidden();

        Livewire::actingAs(User::factory()->create())
            ->test(ServiceCockpit::class)
            ->call('openResolveModal', $ticket->id)
            ->assertForbidden();
    }

    public function test_quick_resolve_is_forbidden_when_policy_denies_update(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket(['status' => TicketStatus::Open, 'owner_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(ServiceCockpit::class)
            ->call('openResolveModal', $ticket->id);

        $this->denyAbilities([Ticket::class], ['update']);

        $component->call('quickResolveTicket')->assertForbidden();

        $this->assertSame(TicketStatus::Open, $ticket->fresh()?->status);
    }

    public function test_claim_ticket_returns_404_for_unknown_ticket(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ServiceCockpit::class)
            ->call('claimTicket', 999999)
            ->assertNotFound();
    }

    public function test_private_canned_responses_of_other_agents_cannot_be_inserted(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ticket = $this->ticket(['status' => TicketStatus::Open]);

        $private = CannedResponse::create([
            'title' => 'Secret macro',
            'shortcut' => '!secret',
            'content' => 'Private wording',
            'is_shared' => false,
            'user_id' => $owner->id,
        ]);

        Livewire::actingAs($other)
            ->test(ServiceCockpit::class)
            ->call('openReplyModal', $ticket->id)
            ->call('insertCannedResponse', $private->id)
            ->assertSet('replyBody', '');
    }

    public function test_service_cockpit_never_falls_back_to_user_one(): void
    {
        $ticket = $this->ticket();

        Livewire::test(ServiceCockpit::class)->assertForbidden();

        $this->assertNull($ticket->fresh()?->owner_id);
    }

    public function test_service_cockpit_defaults_selected_user_to_the_signed_in_user(): void
    {
        User::factory()->create();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ServiceCockpit::class)
            ->assertSet('selectedUserId', $user->id);
    }

    public function test_move_ticket_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Ticket::class], ['update']);
        $ticket = $this->ticket(['status' => TicketStatus::Open]);

        Livewire::actingAs(User::factory()->create())
            ->test(KanbanTickets::class)
            ->call('moveTicket', $ticket->id, TicketStatus::Resolved->value)
            ->assertForbidden();

        $this->assertSame(TicketStatus::Open, $ticket->fresh()?->status);
    }

    public function test_move_deal_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Deal::class], ['update']);
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);
        $deal = Deal::factory()->create([
            'pipeline_id' => $pipeline->id,
            'stage_id' => $pipeline->stages[0]->id,
            'status' => DealStatus::Open,
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(KanbanDeals::class)
            ->call('moveDeal', $deal->id, $pipeline->stages[3]->id)
            ->assertForbidden();

        $this->assertSame($pipeline->stages[0]->id, $deal->fresh()?->stage_id);
    }

    public function test_move_deal_returns_404_for_unknown_deal(): void
    {
        $pipeline = Pipeline::factory()->withStages()->create(['is_default' => true]);

        Livewire::actingAs(User::factory()->create())
            ->test(KanbanDeals::class)
            ->call('moveDeal', 999999, $pipeline->stages[1]->id)
            ->assertNotFound();
    }

    public function test_merge_contacts_is_forbidden_when_policy_denies_delete(): void
    {
        $this->denyAbilities([Contact::class], ['delete']);
        $primary = Contact::factory()->create(['email' => 'dupe@example.com']);
        $secondary = Contact::factory()->create(['email' => 'dupe2@example.com']);

        Livewire::actingAs(User::factory()->create())
            ->test(DataQuality::class)
            ->call('mergeContacts', $primary->id, $secondary->id)
            ->assertForbidden();

        $this->assertNotNull(Contact::query()->find($secondary->id));
    }

    public function test_merge_companies_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Company::class], ['update']);
        $primary = Company::factory()->create(['name' => 'Acme']);
        $secondary = Company::factory()->create(['name' => 'Acme Inc']);

        Livewire::actingAs(User::factory()->create())
            ->test(DataQuality::class)
            ->call('mergeCompanies', $primary->id, $secondary->id)
            ->assertForbidden();

        $this->assertNotNull(Company::query()->find($secondary->id));
    }

    public function test_send_campaign_now_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Campaign::class], ['update']);
        $campaign = Campaign::create([
            'name' => 'Launch',
            'subject' => 'Live now',
            'sender_name' => 'Odden',
            'sender_email' => 'news@odden.test',
            'status' => CampaignStatus::Draft,
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(MarketingCockpit::class)
            ->call('sendCampaignNow', $campaign->id)
            ->assertForbidden();

        $this->assertSame(CampaignStatus::Draft, $campaign->fresh()?->status);
    }

    public function test_recalculate_company_intent_is_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Company::class], ['update']);
        $company = Company::factory()->create(['account_tier' => 'tier_1']);

        Livewire::actingAs(User::factory()->create())
            ->test(AbmCockpit::class)
            ->call('recalculateCompany', $company->id)
            ->assertForbidden();
    }

    public function test_recalculate_all_is_forbidden_when_policy_denies_update_on_any_target_account(): void
    {
        $this->denyAbilities([Company::class], ['update']);
        Company::factory()->create(['account_tier' => 'tier_1', 'intent_score' => 0]);

        Livewire::actingAs(User::factory()->create())
            ->test(AbmCockpit::class)
            ->call('recalculateAll')
            ->assertForbidden();
    }

    public function test_sales_cockpit_methods_are_forbidden_when_policy_denies_update(): void
    {
        $this->denyAbilities([Contact::class, Activity::class], ['update']);
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['lead_status' => LeadStatus::New]);

        $sequence = SalesSequence::query()->create([
            'name' => 'Outreach',
            'is_active' => true,
            'steps' => [
                ['step' => 1, 'type' => 'email', 'delay_days' => 0, 'title' => 'Pitch'],
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
        $activity = Activity::query()->create([
            'type' => ActivityType::Task,
            'title' => 'Call back',
            'status' => ActivityStatus::Pending,
            'subject_type' => $contact->getMorphClass(),
            'subject_id' => $contact->id,
        ]);

        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->call('advanceEnrollment', $enrollment->id)->assertForbidden();
        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->call('completeActivity', $activity->id)->assertForbidden();
        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->call('logQuickTouch', $contact->id, 'call')->assertForbidden();
        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->call('openCallModal', $contact->id)->assertForbidden();
        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->set('meetingContactId', $contact->id)
            ->call('saveMeetingLog')->assertForbidden();
        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->set('callContactId', $contact->id)
            ->call('saveCallLog')->assertForbidden();

        $this->assertSame(1, $enrollment->fresh()?->current_step);
        $this->assertSame(ActivityStatus::Pending, $activity->fresh()?->status);
        $this->assertSame(LeadStatus::New, $contact->fresh()?->lead_status);
        $this->assertSame(1, Activity::query()->count());
    }

    public function test_methods_still_work_when_a_policy_allows(): void
    {
        $this->denyAbilities([Ticket::class, Contact::class], []);
        $user = User::factory()->create();
        $ticket = $this->ticket();
        $primary = Contact::factory()->create(['email' => 'a@example.com']);
        $secondary = Contact::factory()->create(['email' => 'b@example.com']);

        Livewire::actingAs($user)
            ->test(ServiceCockpit::class)
            ->call('claimTicket', $ticket->id)
            ->assertSuccessful();

        Livewire::actingAs($user)
            ->test(DataQuality::class)
            ->call('mergeContacts', $primary->id, $secondary->id)
            ->assertSuccessful();

        $this->assertSame($user->id, $ticket->fresh()?->owner_id);
        $this->assertNull(Contact::query()->find($secondary->id));
    }

    public function test_completing_an_activity_requires_update_on_the_record_it_belongs_to(): void
    {
        // No Activity policy: the activity's contact decides.
        $this->denyAbilities([Contact::class], ['update']);
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $activity = Activity::query()->create([
            'type' => ActivityType::Task,
            'title' => 'Call back',
            'status' => ActivityStatus::Pending,
            'subject_type' => $contact->getMorphClass(),
            'subject_id' => $contact->id,
        ]);

        Livewire::actingAs($user)->test(SalesCockpit::class)
            ->call('completeActivity', $activity->id)->assertForbidden();

        $this->assertSame(ActivityStatus::Pending, $activity->fresh()->status);
    }
}
