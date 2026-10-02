<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Odden\Core\Enums\LifecycleStage;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;
use Odden\Filament\Resources\ContactResource\Pages\ViewContact;
use Odden\Filament\Resources\RelationManagers\ActivitiesRelationManager;
use Odden\Filament\Resources\RelationManagers\PropertyHistoryRelationManager;
use Odden\Filament\Tests\Fixtures\User;

class ContactResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_contacts_index(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/admin/contacts');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_create_contact_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/contacts/create');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_contact_view_page(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@navy.mil',
            'lifecycle_stage' => LifecycleStage::Customer,
        ]);

        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");

        $response->assertSuccessful();
        $response->assertSee('Grace');
        $response->assertSee('Hopper');
    }

    public function test_contact_view_page_renders_associated_companies(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $company = Company::factory()->create([
            'name' => 'Cyberdyne Systems',
        ]);

        $contact->associateWith($company, 'primary');

        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");

        $response->assertSuccessful();
        $response->assertSee('Cyberdyne Systems');
    }

    public function test_contact_activities_relation_manager_renders_activities(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $contact->logNote('Met with founder regarding custom SLA requirements');

        Livewire::actingAs($user)
            ->test(ActivitiesRelationManager::class, [
                'ownerRecord' => $contact,
                'pageClass' => ViewContact::class,
            ])
            ->assertSuccessful()
            ->assertSee('Met with founder regarding custom SLA requirements');
    }

    public function test_contact_property_history_relation_manager_renders_history(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'lifecycle_stage' => LifecycleStage::Lead,
        ]);

        $contact->update(['lifecycle_stage' => LifecycleStage::Customer]);

        Livewire::actingAs($user)
            ->test(PropertyHistoryRelationManager::class, [
                'ownerRecord' => $contact,
                'pageClass' => ViewContact::class,
            ])
            ->assertSuccessful()
            ->assertSee('lifecycle_stage');
    }
}
