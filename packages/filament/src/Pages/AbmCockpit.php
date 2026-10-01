<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Models\Company;
use Focal\Marketing\Actions\CalculateCompanyIntentScoreAction;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class AbmCockpit extends Page
{
    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $navigationLabel = 'ABM Cockpit';

    protected static ?string $title = 'ABM & Target Account Intent Cockpit';

    protected string $view = 'focal-filament::pages.abm-cockpit';

    public string $activeTier = 'all';

    public function setTier(string $tier): void
    {
        $this->activeTier = $tier;
        unset($this->accounts);
    }

    public function getTotalTargetAccountsProperty(): int
    {
        return Company::query()
            ->whereIn('account_tier', ['tier_1', 'tier_2'])
            ->count();
    }

    public function getSurgingAccountsCountProperty(): int
    {
        return Company::query()
            ->where('intent_surge', true)
            ->count();
    }

    public function getTier1AccountsCountProperty(): int
    {
        return Company::query()
            ->where('account_tier', 'tier_1')
            ->count();
    }

    public function getTier2AccountsCountProperty(): int
    {
        return Company::query()
            ->where('account_tier', 'tier_2')
            ->count();
    }

    public function getAverageIntentScoreProperty(): float
    {
        $avg = (float) Company::query()
            ->whereIn('account_tier', ['tier_1', 'tier_2'])
            ->avg('intent_score');

        return round($avg, 1);
    }

    public function getTotalBuyingCommitteeProperty(): int
    {
        return (int) Company::query()
            ->whereIn('account_tier', ['tier_1', 'tier_2'])
            ->sum('buying_committee_size');
    }

    /**
     * @return Collection<int, Company>
     */
    public function getAccountsProperty(): Collection
    {
        $query = Company::query()
            ->with(['owner', 'contacts']);

        match ($this->activeTier) {
            'surging' => $query->where('intent_surge', true),
            'tier_1' => $query->where('account_tier', 'tier_1'),
            'tier_2' => $query->where('account_tier', 'tier_2'),
            default => $query->where(function ($q): void {
                $q->whereIn('account_tier', ['tier_1', 'tier_2'])
                    ->orWhere('intent_surge', true);
            }),
        };

        return $query
            ->orderByDesc('intent_score')
            ->orderByDesc('intent_surge')
            ->take(25)
            ->get();
    }

    public function recalculateAll(CalculateCompanyIntentScoreAction $action): void
    {
        /** @var Collection<int, Company> $accounts */
        $accounts = Company::query()
            ->whereNotNull('account_tier')
            ->orWhere('intent_surge', true)
            ->get();

        foreach ($accounts as $account) {
            $action->execute($account);
        }

        Notification::make()
            ->title('ABM Intent Scores Recalculated')
            ->body("Processed intent signals and buying committee engagement across {$accounts->count()} target accounts.")
            ->success()
            ->send();
    }

    public function recalculateCompany(int $companyId, CalculateCompanyIntentScoreAction $action): void
    {
        /** @var Company|null $company */
        $company = Company::query()->find($companyId);

        if ($company === null) {
            Notification::make()->title('Account not found')->danger()->send();

            return;
        }

        $updated = $action->execute($company);

        $surgeNotice = $updated->intent_surge ? ' (Surging Intent 🔥)' : '';

        Notification::make()
            ->title("Intent Updated: {$updated->name}")
            ->body("New Intent Score: {$updated->intent_score}, Committee Size: {$updated->buying_committee_size}{$surgeNotice}.")
            ->success()
            ->send();
    }
}
