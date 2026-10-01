<?php

declare(strict_types=1);

use Focal\Core\Actions\AssociateRecordsAction;
use Focal\Core\Actions\CreateAssociationTypeAction;
use Focal\Core\Enums\AssociationCardinality;
use Focal\Core\Exceptions\CardinalityViolationException;
use Focal\Core\Models\AssociationType;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;

it('creates association types with labels and cardinality rules', function () {
    $action = app(CreateAssociationTypeAction::class);

    $type = $action->execute([
        'name' => 'billing_contact',
        'label' => 'Billing Contact',
        'reverse_label' => 'Billed Company',
        'cardinality' => AssociationCardinality::OneToOne,
        'from_record_type' => (new Company)->getMorphClass(),
        'to_record_type' => (new Contact)->getMorphClass(),
    ]);

    expect($type)->toBeInstanceOf(AssociationType::class)
        ->and($type->name)->toBe('billing_contact')
        ->and($type->cardinality)->toBe(AssociationCardinality::OneToOne)
        ->and($type->label)->toBe('Billing Contact')
        ->and($type->reverse_label)->toBe('Billed Company');
});

it('attaches association type and label when associating records', function () {
    $type = AssociationType::create([
        'name' => 'primary_decision_maker',
        'label' => 'Primary Decision Maker',
        'reverse_label' => 'Associated Company',
        'cardinality' => AssociationCardinality::OneToMany,
    ]);

    $company = Company::factory()->create();
    $contact = Contact::factory()->create();

    $association = $company->associateWith($contact, $type);

    expect($association->association_type_id)->toBe($type->id)
        ->and($association->label)->toBe('Primary Decision Maker')
        ->and($association->associationType->id)->toBe($type->id);

    // Retrieve via getAssociatedByLabel
    $decisionMakers = $company->getAssociatedByLabel(Contact::class, 'Primary Decision Maker');
    expect($decisionMakers)->toHaveCount(1)
        ->and($decisionMakers->first()->id)->toBe($contact->id);
});

it('enforces OneToOne cardinality constraints strictly', function () {
    $type = AssociationType::create([
        'name' => 'exclusive_billing_contact',
        'label' => 'Exclusive Billing Contact',
        'cardinality' => AssociationCardinality::OneToOne,
    ]);

    $company = Company::factory()->create();
    $contact1 = Contact::factory()->create();
    $contact2 = Contact::factory()->create();

    $action = app(AssociateRecordsAction::class);

    // First association succeeds
    $action->execute($company, $contact1, $type);

    // Associating same company with another contact of same OneToOne type must fail
    expect(fn () => $action->execute($company, $contact2, $type))
        ->toThrow(CardinalityViolationException::class);
});

it('enforces OneToMany cardinality constraints preventing child re-parenting', function () {
    $type = AssociationType::create([
        'name' => 'parent_subsidiary',
        'label' => 'Parent Company',
        'reverse_label' => 'Subsidiary',
        'cardinality' => AssociationCardinality::OneToMany,
    ]);

    $parent1 = Company::factory()->create(['name' => 'Alphabet']);
    $parent2 = Company::factory()->create(['name' => 'Meta']);
    $subsidiary = Company::factory()->create(['name' => 'DeepMind']);

    $action = app(AssociateRecordsAction::class);

    // Link subsidiary to parent 1 (One-to-Many)
    $action->execute($parent1, $subsidiary, $type);

    // Attempting to link same subsidiary to parent 2 under same OneToMany type must fail
    expect(fn () => $action->execute($parent2, $subsidiary, $type))
        ->toThrow(CardinalityViolationException::class);
});

it('resolves contextual bidirectional labels correctly', function () {
    $type = AssociationType::create([
        'name' => 'partner_referral',
        'label' => 'Referred Partner',
        'reverse_label' => 'Originating Agency',
        'cardinality' => AssociationCardinality::ManyToMany,
        'from_record_type' => (new Company)->getMorphClass(),
        'to_record_type' => (new Contact)->getMorphClass(),
    ]);

    $company = Company::factory()->create();
    $contact = Contact::factory()->create();

    expect($type->getLabelFor($company))->toBe('Referred Partner')
        ->and($type->getLabelFor($contact))->toBe('Originating Agency');
});
