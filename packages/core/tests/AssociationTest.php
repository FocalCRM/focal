<?php

declare(strict_types=1);

namespace Focal\Core\Tests;

use Focal\Core\Actions\AssociateRecordsAction;
use Focal\Core\Events\RecordsAssociated;
use Focal\Core\Models\Company;
use Focal\Core\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class AssociationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_associate_contact_and_company_bidirectionally(): void
    {
        $contact = Contact::factory()->create();
        $company = Company::factory()->create();

        $this->assertFalse($contact->isAssociatedWith($company));

        $association = $contact->associateWith($company, 'primary');

        $this->assertNotNull($association);
        $this->assertTrue($contact->isAssociatedWith($company));
        $this->assertTrue($company->isAssociatedWith($contact));

        // Test bi-directional relationship fetching
        $contactCompanies = $contact->companies;
        $this->assertCount(1, $contactCompanies);
        $this->assertTrue($contactCompanies->first()->is($company));

        $companyContacts = $company->contacts;
        $this->assertCount(1, $companyContacts);
        $this->assertTrue($companyContacts->first()->is($contact));
    }

    public function test_associate_records_action_dispatches_event(): void
    {
        Event::fake([RecordsAssociated::class]);

        $contact = Contact::factory()->create();
        $company = Company::factory()->create();

        $action = new AssociateRecordsAction;
        $association = $action->execute($contact, $company, 'billing');

        $this->assertSame('billing', $association->type);

        Event::assertDispatched(RecordsAssociated::class);
    }

    public function test_can_dissociate_records(): void
    {
        $contact = Contact::factory()->create();
        $company = Company::factory()->create();

        $contact->associateWith($company, 'primary');
        $this->assertTrue($contact->isAssociatedWith($company));

        $contact->dissociateFrom($company);
        $this->assertFalse($contact->isAssociatedWith($company));
        $this->assertCount(0, $contact->companies);
    }

    public function test_association_checks_only_match_the_two_records(): void
    {
        $contact = Contact::factory()->create();
        $otherContact = Contact::factory()->create();
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        // Unrelated associations that share one side with the records being checked.
        $otherContact->associateWith($company);
        $contact->associateWith($otherCompany);

        $this->assertFalse($contact->isAssociatedWith($company));
        $this->assertFalse($company->isAssociatedWith($contact));

        $this->assertSame(0, $contact->dissociateFrom($company));
        $this->assertTrue($otherContact->isAssociatedWith($company));
        $this->assertTrue($contact->isAssociatedWith($otherCompany));

        $contact->associateWith($company);
        $this->assertSame(1, $company->dissociateFrom($contact));
        $this->assertTrue($otherContact->isAssociatedWith($company));
        $this->assertTrue($contact->isAssociatedWith($otherCompany));
    }
}
