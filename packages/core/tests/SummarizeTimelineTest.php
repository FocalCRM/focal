<?php

declare(strict_types=1);

use Focal\Core\Actions\SummarizeTimelineAction;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;

test('generates executive briefing summary for contact timeline', function () {
    /** @var Contact $contact */
    $contact = Contact::factory()->create([
        'first_name' => 'Sarah',
        'last_name' => 'Connor',
        'email' => 'sarah@cyberdyne.com',
        'lead_score' => 85,
    ]);

    $contact->logActivity(
        type: ActivityType::Meeting,
        title: 'Enterprise Architecture Review'
    );

    $contact->logActivity(
        type: ActivityType::Call,
        title: 'Budget & Pricing Negotiation'
    );

    $briefing = app(SummarizeTimelineAction::class)->execute($contact);

    expect($briefing['title'])->toContain('Sarah Connor')
        ->and($briefing['sentiment'])->toBe('positive')
        ->and($briefing['touchpoints_analyzed'])->toBe(2)
        ->and($briefing['key_milestones'])->toHaveCount(2)
        ->and($briefing['executive_summary'])->toContain('strong account momentum')
        ->and($briefing['recommended_next_action'])->not->toBeEmpty();
});

test('generates at risk briefing for company with low health standing', function () {
    /** @var Company $company */
    $company = Company::factory()->create([
        'name' => 'Distressed Systems Ltd',
        'health_score' => 20,
    ]);

    $briefing = app(SummarizeTimelineAction::class)->execute($company);

    expect($briefing['sentiment'])->toBe('at_risk')
        ->and($briefing['executive_summary'])->toContain('churn or stalling risk')
        ->and($briefing['recommended_next_action'])->toContain('executive sponsor alignment call');
});
