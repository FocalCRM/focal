<?php

declare(strict_types=1);

namespace Odden\Core\Support;

/**
 * Turns a package's route config block (domain, prefix, middleware) into
 * Route::group() attributes, dropping unset values so they don't override defaults.
 */
final class RouteGroup
{
    /**
     * @return array<string, mixed>
     */
    public static function attributes(string $configKey): array
    {
        $attributes = config($configKey, []);

        if (! is_array($attributes)) {
            return [];
        }

        return array_filter($attributes, fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }
}
