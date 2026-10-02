<?php

declare(strict_types=1);

namespace Odden\Core\Support;

class FreemailDomains
{
    /**
     * Common consumer freemail domains that should not be converted into corporate companies.
     *
     * @var list<string>
     */
    protected static array $domains = [
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'yahoo.co.uk',
        'yahoo.fr',
        'yahoo.ca',
        'ymail.com',
        'rocketmail.com',
        'hotmail.com',
        'hotmail.co.uk',
        'hotmail.fr',
        'outlook.com',
        'outlook.fr',
        'live.com',
        'live.co.uk',
        'msn.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'aol.com',
        'aim.com',
        'protonmail.com',
        'proton.me',
        'zoho.com',
        'yandex.com',
        'yandex.ru',
        'mail.com',
        'email.com',
        'gmx.com',
        'gmx.net',
        'gmx.de',
        'mailinator.com',
        'tutanota.com',
        'tuta.com',
        'fastmail.com',
        'inbox.com',
        'hushmail.com',
        'web.de',
        'comcast.net',
        'sbcglobal.net',
        'verizon.net',
        'att.net',
        'bellsouth.net',
        'charter.net',
        'cox.net',
        'earthlink.net',
        'shaw.ca',
        'rogers.com',
        'sympatico.ca',
        'btinternet.com',
        'virginmedia.com',
        'sky.com',
        'orange.fr',
        'wanadoo.fr',
        'free.fr',
        'laposte.net',
        't-online.de',
        'libero.it',
        'virgilio.it',
        'uol.com.br',
        'bol.com.br',
        'terra.com.br',
    ];

    /**
     * Determine if a given domain is a consumer freemail service.
     */
    public static function isFreemail(string $domain): bool
    {
        $normalized = strtolower(trim($domain));

        /** @var list<string> $configured */
        $configured = config('odden-core.freemail_domains', []);

        if (in_array($normalized, $configured, true)) {
            return true;
        }

        return in_array($normalized, static::$domains, true);
    }

    /**
     * Get the default list of freemail domains.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return static::$domains;
    }
}
