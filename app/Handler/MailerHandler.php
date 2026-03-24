<?php

namespace App\Handler;

use App\Enums\EmailStatus;
use App\Enums\RecipientStatus;
use App\Jobs\SendEmailJob;
use App\Models\Contact;
use App\Models\Email;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

class MailerHandler
{
    public static function sendEmail(Email|Model $email): void
    {
        if ($email->recipients()->count() === 0) {
            Notification::make()
                ->title('No recipients')
                ->body('Add at least one address book or contact before sending.')
                ->danger()
                ->send();

            return;
        }

        $email->load('sender');

        $email->recipients()
            ->where('status', '!=', RecipientStatus::Sent)
            ->update([
                'status' => RecipientStatus::Pending,
                'error' => null,
            ]);

        $pendingRecipients = $email->recipients()
            ->where('status', RecipientStatus::Pending)
            ->get();

        $jobs = $pendingRecipients->map(
            fn($recipient) => new SendEmailJob($email, $recipient)
        )->all();

        $batch = Bus::batch($jobs)
            ->then(function () use ($email): void {
                $email->update(['status' => EmailStatus::Sent]);
            })
            ->catch(function () use ($email): void {
                $email->update(['status' => EmailStatus::Failed]);
            })
            ->allowFailures()
            ->dispatch();

        $email->update([
            'status' => EmailStatus::Sending,
            'batch_id' => $batch->id,
        ]);

        Notification::make()
            ->title('Sending started')
            ->body("Dispatched {$pendingRecipients->count()} emails to the queue.")
            ->success()
            ->send();
    }

    public static function syncRecipients(Email|Model $email, array $addressBookIds = [], array $contactIds = []): void
    {
        if ($email->status !== EmailStatus::Draft) {
            return;
        }

        $contacts = collect();

        if (!empty($addressBookIds)) {
            $addressBookContacts = Contact::query()
                ->whereHas('addressBooks', fn($query) => $query->whereIn('address_books.id', $addressBookIds))
                ->get();
            $contacts = $contacts->merge($addressBookContacts);
        }

        if (!empty($contactIds)) {
            $individualContacts = Contact::query()
                ->whereIn('id', $contactIds)
                ->get();
            $contacts = $contacts->merge($individualContacts);
        }

        $contacts = $contacts->unique('id');

        $email->recipients()->delete();

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
