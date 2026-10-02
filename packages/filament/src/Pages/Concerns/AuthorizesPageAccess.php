<?php

declare(strict_types=1);

namespace Focal\Filament\Pages\Concerns;

use Filament\Resources\Resource as FilamentResource;
use Focal\Filament\Support\FocalAuthorization;

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
        return FocalAuthorization::canViewAny(static::getAuthorizationResources());
    }
}
