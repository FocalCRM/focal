<?php

declare(strict_types=1);

namespace Focal\Core\Tests;

use Focal\Core\Actions\CreateCustomObjectDefinitionAction;
use Focal\Core\Actions\CreateCustomObjectRecordAction;
use Focal\Core\Events\CustomObjectDefinitionCreated;
use Focal\Core\Events\CustomObjectRecordCreated;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Focal\Core\Models\CustomObjectRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CustomObjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_custom_object_definition(): void
    {
        Event::fake([CustomObjectDefinitionCreated::class]);

        $action = new CreateCustomObjectDefinitionAction;
        $definition = $action->execute([
            'name' => 'Subscription License',
            'singular_label' => 'Subscription License',
            'plural_label' => 'Subscription Licenses',
            'description' => 'Tracks enterprise software subscription tiers and seat counts.',
            'primary_display_property' => 'license_key',
            'properties_schema' => [
                'license_key' => ['type' => 'string', 'required' => true],
                'seat_count' => ['type' => 'number', 'required' => true],
                'tier' => ['type' => 'string', 'options' => ['basic', 'pro', 'enterprise']],
            ],
        ]);

        $this->assertSame('subscription_license', $definition->name);
        $this->assertSame('Subscription License', $definition->singular_label);
        $this->assertSame('Subscription Licenses', $definition->plural_label);
        $this->assertSame('license_key', $definition->primary_display_property);
        $this->assertDatabaseHas('focal_custom_object_definitions', [
            'id' => $definition->id,
            'name' => 'subscription_license',
        ]);

        Event::assertDispatched(CustomObjectDefinitionCreated::class, fn (CustomObjectDefinitionCreated $e): bool => $e->definition->id === $definition->id);
    }

    public function test_can_create_custom_object_record_and_resolve_display_name(): void
    {
        Event::fake([CustomObjectRecordCreated::class]);

        $definition = (new CreateCustomObjectDefinitionAction)->execute([
            'name' => 'Onboarding Project',
            'singular_label' => 'Onboarding Project',
            'primary_display_property' => 'project_code',
        ]);

        $recordAction = new CreateCustomObjectRecordAction;
        $record = $recordAction->execute($definition, [
            'properties' => [
                'project_code' => 'PRJ-2026-X',
                'target_launch' => '2026-12-01',
            ],
        ]);

        $this->assertSame('PRJ-2026-X', $record->name);
        $this->assertSame($definition->id, $record->definition_id);
        $this->assertSame('PRJ-2026-X', $record->getProperty('project_code'));

        Event::assertDispatched(CustomObjectRecordCreated::class, fn (CustomObjectRecordCreated $e): bool => $e->record->id === $record->id);
    }

    public function test_can_create_record_by_definition_slug_string(): void
    {
        (new CreateCustomObjectDefinitionAction)->execute([
            'name' => 'Asset',
            'singular_label' => 'Asset',
        ]);

        $record = (new CreateCustomObjectRecordAction)->execute('asset', [
            'name' => 'MacBook Pro M4',
            'properties' => [
                'serial_number' => 'C02XYZ123',
            ],
        ]);

        $this->assertSame('MacBook Pro M4', $record->name);
        $this->assertSame('C02XYZ123', $record->getProperty('serial_number'));
    }

    public function test_custom_object_record_supports_property_history_and_querying(): void
    {
        $definition = (new CreateCustomObjectDefinitionAction)->execute([
            'name' => 'Equipment',
            'singular_label' => 'Equipment',
        ]);

        $record = (new CreateCustomObjectRecordAction)->execute($definition, [
            'name' => 'Forklift 1',
            'properties' => [
                'status' => 'active',
                'hours_used' => 120,
            ],
        ]);

        $record->setProperty('status', 'maintenance');
        $record->save();

        $this->assertSame('maintenance', $record->fresh()->getProperty('status'));
        $matched = CustomObjectRecord::whereProperty('status', 'maintenance')->get();
        $this->assertCount(1, $matched);
        $this->assertTrue($matched->first()->is($record));
    }

    public function test_can_associate_custom_object_with_contact_and_company(): void
    {
        $contact = Contact::factory()->create();
        $company = Company::factory()->create();

        $definition = (new CreateCustomObjectDefinitionAction)->execute([
            'name' => 'Warranty',
            'singular_label' => 'Warranty',
        ]);

        $warranty = (new CreateCustomObjectRecordAction)->execute($definition, [
            'name' => 'Enterprise SLA Warranty',
            'properties' => ['level' => 'platinum'],
        ]);

        // Associate warranty with both contact and company
        $warranty->associateWith($contact, 'primary_contact');
        $warranty->associateWith($company, 'guarantor');

        $this->assertTrue($warranty->isAssociatedWith($contact));
        $this->assertTrue($warranty->isAssociatedWith($company));

        $associatedContacts = $warranty->getAssociated(Contact::class);
        $this->assertCount(1, $associatedContacts);
        $this->assertTrue($associatedContacts->first()->is($contact));

        $associatedWarranties = $company->getAssociated(CustomObjectRecord::class);
        $this->assertCount(1, $associatedWarranties);
        $this->assertTrue($associatedWarranties->first()->is($warranty));
    }
}
