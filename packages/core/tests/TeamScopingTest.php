<?php

declare(strict_types=1);

use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;

it('supports team scoping on contacts and companies', function (): void {
    $contactTeam1 = Contact::factory()->create(['team_id' => 1]);
    $contactTeam2 = Contact::factory()->create(['team_id' => 2]);

    $companyTeam1 = Company::factory()->create(['team_id' => 1]);
    $companyTeam2 = Company::factory()->create(['team_id' => 2]);

    expect(Contact::forTeam(1)->pluck('id'))->toContain($contactTeam1->id)
        ->and(Contact::forTeam(1)->pluck('id'))->not->toContain($contactTeam2->id)
        ->and(Company::forTeam(1)->pluck('id'))->toContain($companyTeam1->id)
        ->and(Company::forTeam(1)->pluck('id'))->not->toContain($companyTeam2->id);
});
