<?php

declare(strict_types=1);

namespace Odden\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Odden\Filament\Pages\Concerns\AuthorizesPageAccess;
use Odden\Filament\Resources\CampaignResource;
use Odden\Filament\Resources\LandingPageResource;
use Odden\Filament\Support\OddenAuthorization;
use Odden\Marketing\Models\Campaign;
use Odden\Marketing\Models\LandingPage;
use UnitEnum;

class UtmLinkBuilder extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Link;

    protected static ?string $navigationLabel = 'UTM Link Builder';

    protected static ?string $title = 'Campaign Inbound UTM Tracking URL Generator';

    protected string $view = 'odden-filament::pages.utm-link-builder';

    public string $baseUrl = 'https://odden.test';

    public ?int $selectedLandingPageId = null;

    public ?int $selectedCampaignId = null;

    public string $customCampaign = '';

    public string $utmSource = 'linkedin';

    public string $utmMedium = 'social';

    public string $utmTerm = '';

    public string $utmContent = '';

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            CampaignResource::class,
            LandingPageResource::class,
        ];
    }

    public function mount(): void
    {
        $this->baseUrl = url('/');
    }

    public function updatedSelectedLandingPageId(?int $id): void
    {
        if ($id !== null) {
            /** @var LandingPage|null $lp */
            $lp = OddenAuthorization::query(LandingPageResource::class, LandingPage::class)->find($id);
            if ($lp !== null) {
                $this->baseUrl = $lp->getPublicUrl();
            }
        }
    }

    public function updatedSelectedCampaignId(?int $id): void
    {
        if ($id !== null) {
            /** @var Campaign|null $campaign */
            $campaign = OddenAuthorization::query(CampaignResource::class, Campaign::class)->find($id);
            if ($campaign !== null) {
                $this->customCampaign = Str::slug($campaign->name);
            }
        }
    }

    public function getGeneratedUrlProperty(): string
    {
        $params = [];

        $source = trim($this->utmSource);
        if ($source !== '') {
            $params['utm_source'] = $source;
        }

        $medium = trim($this->utmMedium);
        if ($medium !== '') {
            $params['utm_medium'] = $medium;
        }

        $campaign = trim($this->customCampaign);
        if ($campaign !== '') {
            $params['utm_campaign'] = $campaign;
        }

        $term = trim($this->utmTerm);
        if ($term !== '') {
            $params['utm_term'] = $term;
        }

        $content = trim($this->utmContent);
        if ($content !== '') {
            $params['utm_content'] = $content;
        }

        $base = trim($this->baseUrl);
        if (empty($params)) {
            return $base;
        }

        $query = http_build_query($params);
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.$query;
    }

    /**
     * @return Collection<int, LandingPage>
     */
    public function getLandingPagesProperty(): Collection
    {
        return OddenAuthorization::query(LandingPageResource::class, LandingPage::class)->where('is_published', true)->get();
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getCampaignsProperty(): Collection
    {
        return OddenAuthorization::query(CampaignResource::class, Campaign::class)->orderBy('created_at', 'desc')->take(20)->get();
    }
}
