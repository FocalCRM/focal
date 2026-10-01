<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Actions\FindDuplicateCompaniesAction;
use Focal\Core\Actions\FindDuplicateContactsAction;
use Focal\Core\Actions\MergeCompaniesAction;
use Focal\Core\Actions\MergeContactsAction;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * @property-read array<int, array{match_field: string, match_value: string, contacts: Collection<int, Contact>}> $duplicateContacts
 * @property-read array<int, array{match_field: string, match_value: string, companies: Collection<int, Company>}> $duplicateCompanies
 * @property-read int $totalContactsCount
 * @property-read int $totalCompaniesCount
 * @property-read float $dataCleanlinessScore
 */
class DataQuality extends Page
{
    protected static UnitEnum|string|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static ?string $navigationLabel = 'Data Quality';

    protected static ?string $title = 'CRM Data Quality & Deduplication Command Center';

    protected string $view = 'focal-filament::pages.data-quality';

    public string $activeTab = 'contacts';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * @return array<int, array{match_field: string, match_value: string, contacts: Collection<int, Contact>}>
     */
    public function getDuplicateContactsProperty(): array
    {
        return app(FindDuplicateContactsAction::class)->execute();
    }

    /**
     * @return array<int, array{match_field: string, match_value: string, companies: Collection<int, Company>}>
     */
    public function getDuplicateCompaniesProperty(): array
    {
        return app(FindDuplicateCompaniesAction::class)->execute();
    }

    public function getTotalContactsCountProperty(): int
    {
        return Contact::query()->count();
    }

    public function getTotalCompaniesCountProperty(): int
    {
        return Company::query()->count();
    }

    public function getDataCleanlinessScoreProperty(): float
    {
        $totalRecords = $this->totalContactsCount + $this->totalCompaniesCount;
        if ($totalRecords === 0) {
            return 100.0;
        }

        $duplicateContactsCount = count($this->duplicateContacts);
        $duplicateCompaniesCount = count($this->duplicateCompanies);
        $totalDupes = $duplicateContactsCount + $duplicateCompaniesCount;

        $score = 100 - (($totalDupes / $totalRecords) * 100);

        return round(max(0.0, min(100.0, $score)), 1);
    }

    public function mergeContacts(int $primaryId, int $secondaryId): void
    {
        /** @var Contact|null $primary */
        $primary = Contact::query()->find($primaryId);
        /** @var Contact|null $secondary */
        $secondary = Contact::query()->find($secondaryId);

        if ($primary === null || $secondary === null) {
            Notification::make()->title('Merge Failed')->body('One or more contacts could not be found.')->danger()->send();

            return;
        }

        app(MergeContactsAction::class)->execute($primary, $secondary);

        Notification::make()
            ->title('Contacts Successfully Merged')
            ->body("Merged duplicate {$secondary->email} into {$primary->email}. All history and associations preserved.")
            ->success()
            ->send();
    }

    public function mergeCompanies(int $primaryId, int $secondaryId): void
    {
        /** @var Company|null $primary */
        $primary = Company::query()->find($primaryId);
        /** @var Company|null $secondary */
        $secondary = Company::query()->find($secondaryId);

        if ($primary === null || $secondary === null) {
            Notification::make()->title('Merge Failed')->body('One or more companies could not be found.')->danger()->send();

            return;
        }

        app(MergeCompaniesAction::class)->execute($primary, $secondary);

        Notification::make()
            ->title('Companies Successfully Merged')
            ->body("Merged duplicate {$secondary->name} into {$primary->name}. All contacts and tickets preserved.")
            ->success()
            ->send();
    }
}
