<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;
use Odden\Filament\Pages\DataQuality;
use Odden\Filament\Pages\ServiceCockpit;
use Odden\Filament\Resources\DealResource\Pages\KanbanDeals;
use Odden\Filament\Tests\Fixtures\User;
use Odden\Marketing\Models\Campaign;
use Odden\Sales\Models\Deal;
use Odden\Service\Models\Ticket;
use PHPUnit\Framework\Attributes\DataProvider;

class PageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Each custom page, and a model whose policy's viewAny gates it.
     *
     * @return array<string, array{string, class-string}>
     */
    public static function pages(): array
    {
        return [
            'executive overview' => ['/admin/executive-overview', Deal::class],
            'data quality (contacts)' => ['/admin/data-quality', Contact::class],
            'data quality (companies)' => ['/admin/data-quality', Company::class],
            'sales cockpit' => ['/admin/sales-cockpit', Deal::class],
            'service cockpit' => ['/admin/service-cockpit', Ticket::class],
            'service analytics' => ['/admin/service-analytics', Ticket::class],
            'marketing cockpit' => ['/admin/marketing-cockpit', Campaign::class],
            'abm cockpit' => ['/admin/abm-cockpit', Company::class],
            'marketing attribution' => ['/admin/marketing-attribution', Campaign::class],
            'campaign benchmarking' => ['/admin/campaign-benchmarking', Campaign::class],
            'marketing calendar' => ['/admin/marketing-calendar', Campaign::class],
            'utm link builder' => ['/admin/utm-link-builder', Campaign::class],
            'sender domain health' => ['/admin/sender-domain-health', Campaign::class],
            'deal board' => ['/admin/deals/board', Deal::class],
            'ticket board' => ['/admin/tickets/board', Ticket::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[DataProvider('pages')]
    public function test_page_returns_403_when_policy_denies_view_any(string $url, string $model): void
    {
        $this->denyAbilities([$model], ['viewAny']);

        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    }

    /**
     * @param  class-string  $model
     */
    #[DataProvider('pages')]
    public function test_page_is_accessible_when_policy_allows_view_any(string $url, string $model): void
    {
        $this->denyAbilities([$model], []);

        $this->actingAs(User::factory()->create())->get($url)->assertSuccessful();
    }

    public function test_pages_are_hidden_from_navigation_when_policy_denies_view_any(): void
    {
        $this->denyAbilities([Ticket::class, Contact::class, Deal::class], ['viewAny']);

        $this->actingAs(User::factory()->create());

        $this->assertFalse(ServiceCockpit::canAccess());
        $this->assertFalse(DataQuality::canAccess());
        $this->assertFalse(KanbanDeals::canAccess());

        $this->get('/admin/contacts')->assertForbidden();
        $this->get('/admin')->assertSuccessful()
            ->assertDontSee('Support Cockpit')
            ->assertDontSee('Data Quality');
    }

    public function test_livewire_requests_to_page_are_rejected_when_policy_denies_view_any(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(ServiceCockpit::class);

        $this->denyAbilities([Ticket::class], ['viewAny']);

        $component->call('setActiveTab', 'all')->assertForbidden();
    }

    public function test_pages_require_an_authenticated_user(): void
    {
        $this->assertFalse(ServiceCockpit::canAccess());
        $this->assertFalse(DataQuality::canAccess());
    }
}
