<?php

declare(strict_types=1);

namespace Odden\Filament\Tests;

use Odden\Core\Enums\ListType;
use Odden\Core\Models\Contact;
use Odden\Core\Models\CrmList;
use Odden\Filament\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CrmListResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_lists_index(): void
    {
        $user = User::factory()->create();
        CrmList::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/admin/crm-lists');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_create_list_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/crm-lists/create');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_view_list_and_members(): void
    {
        $user = User::factory()->create();
        $list = CrmList::factory()->create([
            'name' => 'High Value Enterprise Accounts',
            'type' => ListType::Static,
        ]);

        $contact = Contact::factory()->create([
            'first_name' => 'Katherine',
            'last_name' => 'Johnson',
            'email' => 'katherine@nasa.gov',
        ]);

        $list->addMember($contact);

        $response = $this->actingAs($user)->get("/admin/crm-lists/{$list->id}");

        $response->assertSuccessful();
        $response->assertSee('High Value Enterprise Accounts');
        $response->assertSee('Katherine Johnson');
    }
}
