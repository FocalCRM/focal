<?php

declare(strict_types=1);

namespace Focal\Core\Support;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * CSRF middleware to exclude from Focal's cross-site and server-to-server routes.
 *
 * Laravel 13 renamed the CSRF middleware to PreventRequestForgery and keeps
 * ValidateCsrfToken only as a deprecated subclass. withoutMiddleware() matches
 * exact class names, so excluding ValidateCsrfToken alone leaves CSRF enforced on
 * Laravel 13. Exclude every CSRF class that exists in the installed framework.
 */
final class CsrfExemption
{
    /**
     * @return list<class-string>
     */
    public static function middleware(): array
    {
        $preventRequestForgery = 'Illuminate\Foundation\Http\Middleware\PreventRequestForgery';

        return array_values(array_filter(
            [$preventRequestForgery, ValidateCsrfToken::class],
            class_exists(...),
        ));
    }
}
