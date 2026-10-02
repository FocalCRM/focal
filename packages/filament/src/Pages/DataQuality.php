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
use Focal\Filament\Pages\Concerns\AuthorizesPageAccess;
use Focal\Filament\Resources\CompanyResource;
use Focal\Filament\Resources\ContactResource;
use Focal\Filament\Support\FocalAuthorization;
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
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static ?string $navigationLabel = 'Data Quality';

    protected static ?string $title = 'CRM Data Quality & Deduplication Command Center';

    protected string $view = 'focal-filament::pages.data-quality';

    public string $activeTab = 'contacts';

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            ContactResource::class,
            CompanyResource::class,
        ];
    }

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

    /**
     * Merge the secondary contact into the primary. Requires `update` on the primary and `delete` on the secondary.
     */
    public function mergeContacts(int $primaryId, int $secondaryId): void
    {
        abort_if($primaryId === $secondaryId, 422);

        $primary = FocalAuthorization::findAndAuthorize(ContactResource::class, Contact::class, $primaryId, 'update');
        $secondary = FocalAuthorization::findAndAuthorize(ContactResource::class, Contact::class, $secondaryId, 'delete');

        app(MergeContactsAction::class)->execute($primary, $secondary);

        Notification::make()
            ->title('Contacts Successfully Merged')
            ->body("Merged duplicate {$secondary->email} into {$primary->email}. All history and associations preserved.")
            ->success()
            ->send();
    }

    /**
     * Merge the secondary company into the primary. Requires `update` on the primary and `delete` on the secondary.
     */
    public function mergeCompanies(int $primaryId, int $secondaryId): void
    {
        abort_if($primaryId === $secondaryId, 422);

        $primary = FocalAuthorization::findAndAuthorize(CompanyResource::class, Company::class, $primaryId, 'update');
        $secondary = FocalAuthorization::findAndAuthorize(CompanyResource::class, Company::class, $secondaryId, 'delete');

        app(MergeCompaniesAction::class)->execute($primary, $secondary);

        Notification::make()
            ->title('Companies Successfully Merged')
            ->body("Merged duplicate {$secondary->name} into {$primary->name}. All contacts and tickets preserved.")
            ->success()
            ->send();
    }
}
