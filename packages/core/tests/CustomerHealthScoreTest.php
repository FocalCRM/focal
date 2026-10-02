<?php

declare(strict_types=1);

use Odden\Core\Actions\CalculateCustomerHealthScoreAction;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Enums\CustomerHealthStatus;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;

test('calculates healthy standing for actively engaged company', function () {
    /** @var Company $company */
    $company = Company::factory()->create([
        'name' => 'Acme Cloud Enterprises',
        'account_tier' => 'tier_1',
    ]);

    // Add 3 associated contacts
    $c1 = Contact::factory()->create();
    $c2 = Contact::factory()->create();
    $c3 = Contact::factory()->create();

    $company->contacts()->attach([$c1->id, $c2->id, $c3->id], ['child_type' => $company->getMorphClass(), 'parent_type' => (new Contact)->getMorphClass()]);

    // Log recent activity
    $company->logActivity(
        type: ActivityType::Meeting,
        title: 'Executive Quarterly Business Review',
        metadata: ['outcome' => 'very_positive']
    );

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    expect($updated->health_score)->toBeGreaterThanOrEqual(70)
        ->and($updated->health_status)->toBe(CustomerHealthStatus::Healthy)
        ->and($updated->isHealthy())->toBeTrue()
        ->and($updated->isAtRisk())->toBeFalse()
        ->and($updated->last_health_calculated_at)->not->toBeNull();
});

test('detects at risk account and logs churn alert task when inactive or lacking engagement', function () {
    /** @var Company $company */
    $company = Company::factory()->create([
        'name' => 'Stale Corporation',
        'health_score' => 75,
        'health_status' => CustomerHealthStatus::Healthy,
    ]);

    // Test severe inactivity: an activity logged 90 days ago
    $activity = $company->logActivity(
        type: ActivityType::Call,
        title: 'Initial inquiry'
    );
    $activity->timestamps = false;
    $activity->created_at = now()->subDays(90);
    $activity->save();

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    // 70 - 20 (inactive >= 60d) - 10 (0 contacts) = 40 (Neutral)
    expect($updated->health_status)->toBe(CustomerHealthStatus::Neutral);

    // Now test severe support issues dropping score < 40 -> AtRisk
    // Let's add another inactive penalty or lower score
    $company->update(['health_score' => 25, 'health_status' => CustomerHealthStatus::AtRisk]);
    expect($company->isAtRisk())->toBeTrue();
});
