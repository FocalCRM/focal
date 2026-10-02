<?php

declare(strict_types=1);

namespace Odden\Core\Enums;

enum ListType: string
{
    case Static = 'static';
    case Active = 'active';

    /**
     * Get a human-readable label for the list type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Static => 'Static List',
            self::Active => 'Active (Smart) List',
        };
    }
}
