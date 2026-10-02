<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Odden\Core\Enums\LifecycleStage;
use Odden\Core\Events\ContactCreated;
use Odden\Core\Models\Contact;

class CreateContactAction
{
    /**
     * Execute the action to create a contact.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(
        array $attributes,
        bool $autoAssociateCompany = false,
        bool $createCompanyIfMissing = false
    ): Contact {
        if (isset($attributes['email'])) {
            $attributes['email'] = strtolower(trim((string) $attributes['email']));
        }

        if (isset($attributes['lifecycle_stage']) && is_string($attributes['lifecycle_stage'])) {
            $attributes['lifecycle_stage'] = LifecycleStage::from($attributes['lifecycle_stage']);
        }

        $contact = Contact::create($attributes);

        event(new ContactCreated($contact));

        if ($autoAssociateCompany || (bool) config('odden-core.auto_associate_companies', false)) {
            app(AutoAssociateContactCompanyAction::class)->execute($contact, $createCompanyIfMissing);
        }

        return $contact;
    }
}
