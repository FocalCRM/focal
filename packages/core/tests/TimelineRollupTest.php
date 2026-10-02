<?php

declare(strict_types=1);

use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;

it('rolls up activities from associated contacts into company timeline', function (): void {
    $company = Company::factory()->create(['name' => 'Initech']);
    $contact1 = Contact::factory()->create(['first_name' => 'Peter', 'last_name' => 'Gibbons']);
    $contact2 = Contact::factory()->create(['first_name' => 'Milton', 'last_name' => 'Waddams']);

    $contact1->associateWith($company, 'primary');
    $contact2->associateWith($company, 'employee');

    // 1. Log activity directly on company
    $company->logNote('Company created via bulk intake.');

    // 2. Log activities on associated contacts
    $contact1->logCall('Discovery with Peter', 'Discussed TPS reports.');
    $contact2->logNote('Milton requested his red stapler.');

    // Direct company activities should only be 1
    expect($company->activities)->toHaveCount(1);

    // Timeline rollup should include company note + both contact activities = 3
    $timeline = $company->timeline()->get();

    expect($timeline)->toHaveCount(3)
        ->and($timeline->pluck('body')->all())->toContain(
            'Company created via bulk intake.',
            'Discussed TPS reports.',
            'Milton requested his red stapler.'
        );
});
