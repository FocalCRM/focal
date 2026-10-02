<?php

declare(strict_types=1);

namespace Focal\Filament\Support;

use Focal\Marketing\Models\Campaign;
use Focal\Sales\Models\Deal;
use Focal\Service\Models\Ticket;

/**
 * Which optional Focal packages are installed alongside Core.
 *
 * @internal
 */
final class FocalPackages
{
    /** @var array<string, bool> */
    private static array $overrides = [];

    public static function hasSales(): bool
    {
        return self::$overrides['sales'] ?? class_exists(Deal::class);
    }

    public static function hasService(): bool
    {
        return self::$overrides['service'] ?? class_exists(Ticket::class);
    }

    public static function hasMarketing(): bool
    {
        return self::$overrides['marketing'] ?? class_exists(Campaign::class);
    }

    /**
     * Pretend packages are (not) installed. For tests only.
     *
     * @param  array<'sales'|'service'|'marketing', bool>  $packages
     */
    public static function fake(array $packages): void
    {
        self::$overrides = $packages;
    }

    public static function reset(): void
    {
        self::$overrides = [];
    }
}
