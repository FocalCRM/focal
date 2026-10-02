<?php

declare(strict_types=1);

use Odden\Core\Models\Contact;
use Odden\Core\Support\ContactLookup;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('finds a contact ignoring case and surrounding whitespace', function (): void {
    $contact = Contact::factory()->create(['email' => 'dana@example.com']);

    expect(ContactLookup::findByEmail('  Dana@Example.COM '))->id->toBe($contact->id);
});

it('matches legacy contacts saved with mixed case or whitespace', function (): void {
    $contact = Contact::factory()->create(['email' => ' Legacy.User@Example.com']);

    expect(ContactLookup::findByEmail('legacy.user@example.com'))->id->toBe($contact->id);
});

it('uses an indexable exact match before falling back to LOWER(TRIM(email))', function (): void {
    $contact = Contact::factory()->create(['email' => 'dana@example.com']);

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    expect(ContactLookup::findByEmail('Dana@Example.com'))->id->toBe($contact->id);

    expect($queries)->toHaveCount(1)
        ->and($queries[0])->not->toContain('lower(');
});

it('returns null for an empty or unknown email', function (): void {
    expect(ContactLookup::findByEmail('   '))->toBeNull()
        ->and(ContactLookup::findByEmail('nobody@example.com'))->toBeNull();
});

it('creates new contacts with the email lowercased and trimmed, once', function (): void {
    $created = ContactLookup::findOrCreate(' New.Person@Example.com ', ['first_name' => 'New']);
    $again = ContactLookup::findOrCreate('new.person@example.com', ['first_name' => 'Other']);

    expect($created->email)->toBe('new.person@example.com')
        ->and($created->first_name)->toBe('New')
        ->and($again->id)->toBe($created->id)
        ->and(Contact::query()->count())->toBe(1);
});
