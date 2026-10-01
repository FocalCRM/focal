<?php

declare(strict_types=1);

namespace Focal\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Focal\Filament\Pages\AbmCockpit;
use Focal\Filament\Pages\CampaignBenchmarking;
use Focal\Filament\Pages\DataQuality;
use Focal\Filament\Pages\ExecutiveOverview;
use Focal\Filament\Pages\MarketingAttribution;
use Focal\Filament\Pages\MarketingCalendar;
use Focal\Filament\Pages\MarketingCockpit;
use Focal\Filament\Pages\SalesCockpit;
use Focal\Filament\Pages\SenderDomainHealth;
use Focal\Filament\Pages\ServiceAnalytics;
use Focal\Filament\Pages\ServiceCockpit;
use Focal\Filament\Pages\UtmLinkBuilder;
use Focal\Filament\Resources\AdAudienceSyncResource;
use Focal\Filament\Resources\CampaignResource;
use Focal\Filament\Resources\CannedResponseResource;
use Focal\Filament\Resources\CompanyResource;
use Focal\Filament\Resources\ContactResource;
use Focal\Filament\Resources\CrmListResource;
use Focal\Filament\Resources\DealResource;
use Focal\Filament\Resources\KnowledgeArticleResource;
use Focal\Filament\Resources\LandingPageResource;
use Focal\Filament\Resources\LeadRoutingRuleResource;
use Focal\Filament\Resources\LeadScoringRuleResource;
use Focal\Filament\Resources\MarketingAssetResource;
use Focal\Filament\Resources\MarketingEventResource;
use Focal\Filament\Resources\MarketingFormResource;
use Focal\Filament\Resources\MarketingSubscriptionResource;
use Focal\Filament\Resources\MarketingTemplateResource;
use Focal\Filament\Resources\MarketingWorkflowResource;
use Focal\Filament\Resources\NpsSurveyResource;
use Focal\Filament\Resources\PipelineResource;
use Focal\Filament\Resources\PropertyDefinitionResource;
use Focal\Filament\Resources\QuoteResource;
use Focal\Filament\Resources\SalesEmailTemplateResource;
use Focal\Filament\Resources\SalesMeetingLinkResource;
use Focal\Filament\Resources\SalesPlaybookResource;
use Focal\Filament\Resources\SalesQuotaResource;
use Focal\Filament\Resources\SalesSequenceResource;
use Focal\Filament\Resources\SlaPolicyResource;
use Focal\Filament\Resources\TicketResource;
use Focal\Filament\Resources\TicketRoutingRuleResource;
use Focal\Marketing\Models\Campaign;
use Focal\Sales\Models\Deal;
use Focal\Service\Models\Ticket;

class FocalPlugin implements Plugin
{
    public function getId(): string
    {
        return 'focal';
    }

    public function register(Panel $panel): void
    {
        $resources = [
            ContactResource::class,
            CompanyResource::class,
            CrmListResource::class,
            PropertyDefinitionResource::class,
        ];

        if (class_exists(Deal::class)) {
            $resources = array_merge($resources, [
                DealResource::class,
                PipelineResource::class,
                QuoteResource::class,
                SalesQuotaResource::class,
                SalesEmailTemplateResource::class,
                SalesSequenceResource::class,
                SalesPlaybookResource::class,
                SalesMeetingLinkResource::class,
                LeadRoutingRuleResource::class,
            ]);
        }

        if (class_exists(Ticket::class)) {
            $resources = array_merge($resources, [
                TicketResource::class,
                SlaPolicyResource::class,
                KnowledgeArticleResource::class,
                CannedResponseResource::class,
                TicketRoutingRuleResource::class,
            ]);
        }

        if (class_exists(Campaign::class)) {
            $resources = array_merge($resources, [
                CampaignResource::class,
                MarketingTemplateResource::class,
                MarketingFormResource::class,
                LandingPageResource::class,
                MarketingWorkflowResource::class,
                LeadScoringRuleResource::class,
                MarketingSubscriptionResource::class,
                NpsSurveyResource::class,
                MarketingAssetResource::class,
                MarketingEventResource::class,
                AdAudienceSyncResource::class,
            ]);
        }

        $panel->resources($resources);

        $pages = [
            ExecutiveOverview::class,
            DataQuality::class,
        ];

        if (class_exists(Deal::class)) {
            $pages[] = SalesCockpit::class;
        }

        if (class_exists(Ticket::class)) {
            $pages[] = ServiceCockpit::class;
            $pages[] = ServiceAnalytics::class;
        }

        if (class_exists(Campaign::class)) {
            $pages = array_merge($pages, [
                MarketingCockpit::class,
                AbmCockpit::class,
                MarketingAttribution::class,
                CampaignBenchmarking::class,
                MarketingCalendar::class,
                UtmLinkBuilder::class,
                SenderDomainHealth::class,
            ]);
        }

        $panel->pages($pages);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
