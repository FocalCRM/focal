<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Pages\Concerns\AuthorizesPageAccess;
use Focal\Filament\Resources\CampaignResource;
use Focal\Filament\Resources\LandingPageResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Marketing\Models\Campaign;
use Focal\Marketing\Models\LandingPage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class UtmLinkBuilder extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Link;

    protected static ?string $navigationLabel = 'UTM Link Builder';

    protected static ?string $title = 'Campaign Inbound UTM Tracking URL Generator';

    protected string $view = 'focal-filament::pages.utm-link-builder';

    public string $baseUrl = 'https://focal.test';

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
            $lp = FocalAuthorization::query(LandingPageResource::class, LandingPage::class)->find($id);
            if ($lp !== null) {
                $this->baseUrl = $lp->getPublicUrl();
            }
        }
    }

    public function updatedSelectedCampaignId(?int $id): void
    {
        if ($id !== null) {
            /** @var Campaign|null $campaign */
            $campaign = FocalAuthorization::query(CampaignResource::class, Campaign::class)->find($id);
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
        return FocalAuthorization::query(LandingPageResource::class, LandingPage::class)->where('is_published', true)->get();
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getCampaignsProperty(): Collection
    {
        return FocalAuthorization::query(CampaignResource::class, Campaign::class)->orderBy('created_at', 'desc')->take(20)->get();
    }
}
