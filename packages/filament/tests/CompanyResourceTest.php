<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use App\Models\User;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_companies_index(): void
    {
        $user = User::factory()->create();
        Company::factory()->count(2)->create();

        $response = $this->actingAs($user)->get('/admin/companies');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_company_view_page(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'name' => 'Acme Corporation',
            'domain' => 'acme.com',
        ]);

        $response = $this->actingAs($user)->get("/admin/companies/{$company->id}");

        $response->assertSuccessful();
        $response->assertSee('Acme Corporation');
    }

    public function test_company_view_page_renders_associated_contacts(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $contact = Contact::factory()->create([
            'first_name' => 'Miles',
            'last_name' => 'Dyson',
            'email' => 'miles@cyberdyne.com',
        ]);

        $contact->associateWith($company, 'decision_maker');

        $response = $this->actingAs($user)->get("/admin/companies/{$company->id}");

        $response->assertSuccessful();
        $response->assertSee('miles@cyberdyne.com');
    }
}
