<?php

declare(strict_types=1);

use Odden\Core\Actions\CalculateCustomerHealthScoreAction;
use Odden\Core\Actions\SummarizeTimelineAction;
use Odden\Core\Enums\ActivityStatus;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Enums\CustomerHealthStatus;
use Odden\Core\Models\Company;
use Odden\Core\Tests\Fixtures\CrossHubRelations;
use Odden\Core\Tests\Fixtures\Deal;
use Odden\Core\Tests\Fixtures\Ticket;

beforeEach(fn () => CrossHubRelations::install());
afterEach(fn () => CrossHubRelations::uninstall());

/**
 * A company with no activities and no contacts: 70 - 10 - 10 = 50 before deal and ticket signals.
 */
function bareCompany(array $attributes = []): Company
{
    return Company::factory()->create(array_merge([
        'name' => 'Signal Test Inc',
        'health_score' => 70,
        'health_status' => CustomerHealthStatus::Healthy,
    ], $attributes));
}

test('deals registered with resolveRelationUsing count toward the health score', function () {
    $company = bareCompany();
    CrossHubRelations::attachDeal($company, Deal::create(['name' => 'Renewal', 'status' => 'won']));
    CrossHubRelations::attachDeal($company, Deal::create(['name' => 'Expansion', 'status' => 'open']));

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    // 50 + 15 (won) + 10 (open)
    expect($updated->health_score)->toBe(75)
        ->and($updated->health_status)->toBe(CustomerHealthStatus::Healthy);
});

test('a recently lost deal lowers the score', function () {
    $company = bareCompany();
    CrossHubRelations::attachDeal($company, Deal::create(['name' => 'Lost', 'status' => 'lost']));

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    expect($updated->health_score)->toBe(40);
});

test('tickets registered with resolveRelationUsing count toward the health score', function () {
    $company = bareCompany();
    Ticket::create(['subject' => 'Resolved, happy', 'company_id' => $company->id, 'status' => 'resolved', 'csat_rating' => 5]);

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    // 50 + 15 (average CSAT >= 4)
    expect($updated->health_score)->toBe(65);
});

test('deal and ticket signals can drive a company into AtRisk and raise the churn alert task', function () {
    $company = bareCompany();
    CrossHubRelations::attachDeal($company, Deal::create(['name' => 'Lost', 'status' => 'lost']));
    Ticket::create([
        'subject' => 'Outage',
        'company_id' => $company->id,
        'status' => 'open',
        'priority' => 'urgent',
        'is_sla_response_breached' => true,
    ]);

    $updated = app(CalculateCustomerHealthScoreAction::class)->execute($company);

    // 50 - 10 (recent lost deal) - 15 (open urgent ticket) - 20 (SLA breach)
    expect($updated->health_score)->toBe(5)
        ->and($updated->health_status)->toBe(CustomerHealthStatus::AtRisk)
        ->and($updated->isAtRisk())->toBeTrue();

    $task = $updated->activities()->where('type', ActivityType::Task->value)->sole();
    expect($task->title)->toBe('Customer Churn Risk Alert: Signal Test Inc')
        ->and($task->status)->toBe(ActivityStatus::Pending);
});

test('timeline summary sees deals registered with resolveRelationUsing', function () {
    $company = bareCompany(['health_score' => 60]);
    CrossHubRelations::attachDeal($company, Deal::create(['name' => 'Renewal', 'status' => 'won']));

    $briefing = app(SummarizeTimelineAction::class)->execute($company);

    expect($briefing['sentiment'])->toBe('positive')
        ->and($briefing['executive_summary'])->toContain('1 closed-won deal(s)');
});

test('timeline summary sees tickets registered with resolveRelationUsing', function () {
    $company = bareCompany(['health_score' => 60]);
    Ticket::create(['subject' => 'Late', 'company_id' => $company->id, 'is_sla_resolution_breached' => true]);

    $briefing = app(SummarizeTimelineAction::class)->execute($company);

    expect($briefing['sentiment'])->toBe('at_risk')
        ->and($briefing['executive_summary'])->toContain('1 active SLA breach(es)');
});
