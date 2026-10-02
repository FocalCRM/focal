<?php

declare(strict_types=1);

namespace Odden\Core\Enums;

enum LifecycleStage: string
{
    case Subscriber = 'subscriber';
    case Lead = 'lead';
    case MarketingQualifiedLead = 'marketing_qualified_lead';
    case SalesQualifiedLead = 'sales_qualified_lead';
    case Opportunity = 'opportunity';
    case Customer = 'customer';
    case Evangelist = 'evangelist';
    case Other = 'other';

    /**
     * Get a human-readable label for the lifecycle stage.
     */
    public function label(): string
    {
        return match ($this) {
            self::Subscriber => 'Subscriber',
            self::Lead => 'Lead',
            self::MarketingQualifiedLead => 'Marketing Qualified Lead (MQL)',
            self::SalesQualifiedLead => 'Sales Qualified Lead (SQL)',
            self::Opportunity => 'Opportunity',
            self::Customer => 'Customer',
            self::Evangelist => 'Evangelist',
            self::Other => 'Other',
        };
    }
}
