<?php

declare(strict_types=1);

namespace Odden\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Odden\Filament\Pages\Concerns\AuthorizesPageAccess;
use Odden\Filament\Resources\CampaignResource;
use Odden\Marketing\Services\DomainHealthCheckService;
use UnitEnum;

/**
 * @property-read array<string, mixed> $diagnostics
 */
class SenderDomainHealth extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?string $navigationLabel = 'Domain Health (SPF/DKIM)';

    protected static ?string $title = 'Sender Domain Health & Deliverability Diagnostics';

    protected string $view = 'odden-filament::pages.sender-domain-health';

    public string $domain = 'odden.test';

    public string $selector = 'odden';

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            CampaignResource::class,
        ];
    }

    public function mount(): void
    {
        $senderEmail = (string) config('odden-marketing.defaults.sender_email', 'newsletter@odden.test');
        if (str_contains($senderEmail, '@')) {
            $this->domain = explode('@', $senderEmail)[1];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getDiagnosticsProperty(): array
    {
        return app(DomainHealthCheckService::class)->diagnose($this->domain, $this->selector);
    }

    public function checkNow(): void
    {
        Notification::make()
            ->title('DNS Diagnostics Refreshed')
            ->body("Evaluated SPF, DKIM, DMARC, and MX records for {$this->domain}.")
            ->success()
            ->send();
    }
}
