<?php

declare(strict_types=1);

namespace Odden\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Odden\Filament\Pages\AbmCockpit;
use Odden\Filament\Pages\CampaignBenchmarking;
use Odden\Filament\Pages\DataQuality;
use Odden\Filament\Pages\ExecutiveOverview;
use Odden\Filament\Pages\MarketingAttribution;
use Odden\Filament\Pages\MarketingCalendar;
use Odden\Filament\Pages\MarketingCockpit;
use Odden\Filament\Pages\SalesCockpit;
use Odden\Filament\Pages\SenderDomainHealth;
use Odden\Filament\Pages\ServiceAnalytics;
use Odden\Filament\Pages\ServiceCockpit;
use Odden\Filament\Pages\UtmLinkBuilder;
use Odden\Filament\Resources\AdAudienceSyncResource;
use Odden\Filament\Resources\CampaignResource;
use Odden\Filament\Resources\CannedResponseResource;
use Odden\Filament\Resources\CompanyResource;
use Odden\Filament\Resources\ContactResource;
use Odden\Filament\Resources\CrmListResource;
use Odden\Filament\Resources\DealResource;
use Odden\Filament\Resources\KnowledgeArticleResource;
use Odden\Filament\Resources\LandingPageResource;
use Odden\Filament\Resources\LeadRoutingRuleResource;
use Odden\Filament\Resources\LeadScoringRuleResource;
use Odden\Filament\Resources\MarketingAssetResource;
use Odden\Filament\Resources\MarketingEventResource;
use Odden\Filament\Resources\MarketingFormResource;
use Odden\Filament\Resources\MarketingSubscriptionResource;
use Odden\Filament\Resources\MarketingTemplateResource;
use Odden\Filament\Resources\MarketingWorkflowResource;
use Odden\Filament\Resources\NpsSurveyResource;
use Odden\Filament\Resources\PipelineResource;
use Odden\Filament\Resources\PropertyDefinitionResource;
use Odden\Filament\Resources\QuoteResource;
use Odden\Filament\Resources\SalesEmailTemplateResource;
use Odden\Filament\Resources\SalesMeetingLinkResource;
use Odden\Filament\Resources\SalesPlaybookResource;
use Odden\Filament\Resources\SalesQuotaResource;
use Odden\Filament\Resources\SalesSequenceResource;
use Odden\Filament\Resources\SlaPolicyResource;
use Odden\Filament\Resources\TicketResource;
use Odden\Filament\Resources\TicketRoutingRuleResource;
use Odden\Marketing\Models\Campaign;
use Odden\Sales\Models\Deal;
use Odden\Service\Models\Ticket;

class OddenPlugin implements Plugin
{
    public function getId(): string
    {
        return 'odden';
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
