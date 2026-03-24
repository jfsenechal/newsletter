<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Pages;

use App\Enums\RecipientStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\Contact;
use App\Models\Email;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateEmail extends CreateRecord
{
    protected static string $resource = EmailResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Email $email */
        $email = self::getModel()::create($data);

        $this->createRecipients($email);

        return $email;
    }

    private function createRecipients(Email $email): void
    {
        $contacts = collect();

        $addressBookIds = $this->data['address_book_ids'] ?? [];
        if (! empty($addressBookIds)) {
            $addressBookContacts = Contact::query()
                ->whereHas('addressBooks', fn ($query) => $query->whereIn('address_books.id', $addressBookIds))
                ->get();
            $contacts = $contacts->merge($addressBookContacts);
        }

        $contactIds = $this->data['contact_ids'] ?? [];
        if (! empty($contactIds)) {
            $individualContacts = Contact::query()
                ->whereIn('id', $contactIds)
                ->get();
            $contacts = $contacts->merge($individualContacts);
        }

        $contacts = $contacts->unique('id');

        foreach ($contacts as $contact) {
            $email->recipients()->create([
                'contact_id' => $contact->id,
                'email_address' => $contact->email,
                'name' => mb_trim("{$contact->first_name} {$contact->last_name}"),
                'status' => RecipientStatus::Pending,
            ]);
        }

        $email->update(['total_count' => $contacts->count()]);
    }
}
