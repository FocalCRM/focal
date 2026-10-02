<?php

declare(strict_types=1);

use Odden\Core\Enums\LifecycleStage;
use Odden\Core\Enums\ListType;
use Odden\Core\Models\Contact;
use Odden\Core\Models\CrmList;

it('can manage static list members', function (): void {
    $list = CrmList::create([
        'name' => 'Q4 Event Invitees',
        'entity_type' => 'contact',
        'type' => ListType::Static,
    ]);

    $contact1 = Contact::factory()->create();
    $contact2 = Contact::factory()->create();

    $list->addMember($contact1);

    expect($list->hasMember($contact1))->toBeTrue()
        ->and($list->hasMember($contact2))->toBeFalse()
        ->and($list->contacts)->toHaveCount(1);

    $list->removeMember($contact1);

    expect($list->hasMember($contact1))->toBeFalse();
});

it('evaluates active smart list criteria dynamically', function (): void {
    $activeList = CrmList::create([
        'name' => 'High Value Leads',
        'entity_type' => 'contact',
        'type' => ListType::Active,
        'criteria' => [
            [
                'property' => 'lifecycle_stage',
                'operator' => '=',
                'value' => 'lead',
            ],
            [
                'property' => 'lead_score',
                'operator' => '>=',
                'value' => 50,
            ],
        ],
    ]);

    // Matching contact: lead with score 80
    $match = Contact::factory()->create([
        'lifecycle_stage' => LifecycleStage::Lead,
        'properties' => ['lead_score' => 80],
    ]);

    // Non-matching 1: lead with score 30
    Contact::factory()->create([
        'lifecycle_stage' => LifecycleStage::Lead,
        'properties' => ['lead_score' => 30],
    ]);

    // Non-matching 2: customer with score 90
    Contact::factory()->create([
        'lifecycle_stage' => LifecycleStage::Customer,
        'properties' => ['lead_score' => 90],
    ]);

    $syncedCount = $activeList->syncActiveMembers();

    expect($syncedCount)->toBe(1)
        ->and($activeList->contacts)->toHaveCount(1)
        ->and($activeList->contacts->first()->id)->toBe($match->id);
});
