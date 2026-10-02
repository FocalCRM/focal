<?php

declare(strict_types=1);

namespace Odden\Filament\Pages\Concerns;

use Filament\Resources\Resource as FilamentResource;
use Odden\Filament\Support\OddenAuthorization;

/**
 * Gates a custom page on `viewAny` for every resource whose data it shows.
 *
 * Filament calls `canAccess()` for the navigation item, on mount (403) and on every
 * Livewire request to the page (403).
 */
trait AuthorizesPageAccess
{
    /**
     * @return list<class-string<FilamentResource>>
     */
    abstract protected static function getAuthorizationResources(): array;

    public static function canAccess(): bool
    {
        return OddenAuthorization::canViewAny(static::getAuthorizationResources());
    }
}
