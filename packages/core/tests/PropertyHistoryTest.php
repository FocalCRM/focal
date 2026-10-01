<?php

declare(strict_types=1);

use Focal\Core\Enums\LifecycleStage;
use Focal\Core\Models\Contact;

it('tracks changes to standard properties in audit history', function (): void {
    $contact = Contact::factory()->create([
        'email' => 'original@example.com',
        'lifecycle_stage' => LifecycleStage::Lead,
    ]);

    $contact->update([
        'email' => 'updated@example.com',
        'lifecycle_stage' => LifecycleStage::Customer,
    ]);

    $history = $contact->propertyHistory;

    expect($history)->toHaveCount(2);

    $emailAudit = $history->firstWhere('property_name', 'email');
    expect($emailAudit)->not->toBeNull()
        ->and($emailAudit->old_value)->toBe('original@example.com')
        ->and($emailAudit->new_value)->toBe('updated@example.com');

    $stageAudit = $history->firstWhere('property_name', 'lifecycle_stage');
    expect($stageAudit)->not->toBeNull()
        ->and($stageAudit->old_value)->toBe('lead')
        ->and($stageAudit->new_value)->toBe('customer');
});

it('tracks changes to dynamic custom properties in audit history', function (): void {
    $contact = Contact::factory()->create([
        'properties' => [
            'lead_score' => 10,
            'plan' => 'starter',
        ],
    ]);

    // Update custom property
    $contact->setProperty('lead_score', 85)->save();

    $history = $contact->propertyHistory;

    $scoreAudit = $history->firstWhere('property_name', 'lead_score');
    expect($scoreAudit)->not->toBeNull()
        ->and($scoreAudit->old_value)->toBe('10')
        ->and($scoreAudit->new_value)->toBe('85');

    // Untouched property 'plan' should not have an audit record
    expect($history->firstWhere('property_name', 'plan'))->toBeNull();
});
