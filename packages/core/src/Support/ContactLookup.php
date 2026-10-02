<?php

declare(strict_types=1);

namespace Focal\Core\Support;

use Focal\Core\Models\Contact;

/**
 * Finds and creates contacts by email address, ignoring case and surrounding whitespace.
 *
 * Every entry point that turns an email address into a contact (the Service portal, chat widget
 * and inbound email, Sales meeting booking) goes through here, so one person is one contact
 * however they type their address. New contacts are stored with the email lowercased and
 * trimmed. The lookup first tries an exact match on the normalized address, which can use the
 * email index and finds every contact stored this way; only when that finds nothing does it
 * compare LOWER(TRIM(email)), which also matches older contacts saved with mixed case or
 * whitespace.
 */
class ContactLookup
{
    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * The contact whose email matches, ignoring case and surrounding whitespace.
     *
     * The oldest contact stored with exactly the normalized address wins; failing that, the
     * oldest contact whose address matches once lowercased and trimmed.
     */
    public static function findByEmail(string $email): ?Contact
    {
        $normalized = self::normalizeEmail($email);

        if ($normalized === '') {
            return null;
        }

        $key = (new Contact)->getKeyName();

        /** @var Contact|null $contact */
        $contact = Contact::query()
            ->where('email', $normalized)
            ->orderBy($key)
            ->first();

        if ($contact !== null) {
            return $contact;
        }

        /** @var Contact|null $contact */
        $contact = Contact::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalized])
            ->orderBy($key)
            ->first();

        return $contact;
    }

    /**
     * The matching contact, or a new one with the normalized email and the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function findOrCreate(string $email, array $attributes = []): Contact
    {
        $contact = self::findByEmail($email);

        if ($contact !== null) {
            return $contact;
        }

        /** @var Contact $created */
        $created = Contact::query()->create([...$attributes, 'email' => self::normalizeEmail($email)]);

        return $created;
    }
}
