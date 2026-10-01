<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Focal\Core\Support\FreemailDomains;
use Illuminate\Support\Str;

class ExtractCorporateDomainAction
{
    /**
     * Common subdomains to strip when resolving corporate domain.
     *
     * @var list<string>
     */
    protected array $subdomainsToStrip = [
        'mail.',
        'email.',
        'smtp.',
        'webmail.',
        'mx.',
        'exchange.',
        'pop.',
        'imap.',
    ];

    /**
     * Extract and normalize a corporate domain from an email address.
     * Returns null if email is invalid or belongs to a freemail provider.
     */
    public function execute(string $email): ?string
    {
        $trimmed = strtolower(trim($email));

        if (! filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $parts = explode('@', $trimmed);
        if (count($parts) !== 2) {
            return null;
        }

        $domain = trim($parts[1]);

        // Strip leading www.
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        // Strip common email server subdomains (e.g. mail.acme.com -> acme.com)
        foreach ($this->subdomainsToStrip as $prefix) {
            if (str_starts_with($domain, $prefix) && substr_count($domain, '.') >= 2) {
                $domain = substr($domain, strlen($prefix));
                break;
            }
        }

        // Check if domain is a known freemail domain
        if (FreemailDomains::isFreemail($domain)) {
            return null;
        }

        // Ensure domain has at least one dot and is valid
        if (! str_contains($domain, '.') || strlen($domain) < 4) {
            return null;
        }

        return $domain;
    }

    /**
     * Derive a human-readable company name from a corporate domain.
     */
    public function deriveCompanyName(string $domain): string
    {
        $clean = preg_replace('#^https?://#', '', strtolower(trim($domain))) ?? '';
        $clean = preg_replace('#^www\.#', '', $clean) ?? '';
        $clean = explode('/', $clean)[0];

        // Strip common TLD extensions (.com, .org, .net, .co.uk, etc.)
        $parts = explode('.', $clean);
        $namePart = $parts[0];

        // Replace hyphens and underscores with spaces
        $name = str_replace(['-', '_'], ' ', $namePart);

        return Str::headline($name);
    }
}
