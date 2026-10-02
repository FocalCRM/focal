<?php

declare(strict_types=1);

namespace Odden\Core\Enums;

enum CustomerHealthStatus: string
{
    case Healthy = 'healthy';
    case Neutral = 'neutral';
    case AtRisk = 'at_risk';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Neutral => 'Neutral',
            self::AtRisk => 'At Risk',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Healthy => 'success',
            self::Neutral => 'warning',
            self::AtRisk => 'danger',
        };
    }

    public function badgeIcon(): string
    {
        return match ($this) {
            self::Healthy => 'heroicon-m-check-circle',
            self::Neutral => 'heroicon-m-minus-circle',
            self::AtRisk => 'heroicon-m-exclamation-triangle',
        };
    }
}
