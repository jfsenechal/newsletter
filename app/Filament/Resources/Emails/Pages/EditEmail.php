<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Pages;

use App\Enums\EmailStatus;
use App\Enums\RecipientStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Jobs\SendEmailJob;
use App\Models\Contact;
use App\Models\Email;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Bus;

final class EditEmail extends EditRecord
{
    protected static string $resource = EmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send Newsletter')
                ->modalDescription(fn (): string => "This will send the email to {$this->record->total_count} recipients. Continue?")
                ->visible(fn (): bool => $this->record->status === EmailStatus::Draft || $this->record->status === EmailStatus::Failed)
                ->action(fn () => $this->sendEmail()),
            Action::make('progress')
                ->label(fn (): string => "Sent: {$this->record->sent_count}/{$this->record->total_count}")
                ->icon('heroicon-o-chart-bar')
                ->color(fn (): string => match ($this->record->status) {
                    EmailStatus::Sending => 'warning',
                    EmailStatus::Sent => 'success',
                    EmailStatus::Failed => 'danger',
                    default => 'gray',
                })
                ->disabled()
                ->visible(fn (): bool => $this->record->status !== EmailStatus::Draft),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['address_book_ids'], $data['contact_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncRecipients();
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        /** @var Email $email */
        $email = $this->record;

        $addressBookIds = $email->recipients()
            ->whereNotNull('contact_id')
            ->with('contact.addressBooks')
            ->get()
            ->pluck('contact.addressBooks')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $contactIds = $email->recipients()
            ->whereNotNull('contact_id')
            ->pluck('contact_id')
            ->all();

        $this->data['address_book_ids'] = $addressBookIds;
        $this->data['contact_ids'] = $contactIds;
    }

    private function sendEmail(): void
    {
        /** @var Email $email */
        $email = $this->record;

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
            fn ($recipient) => new SendEmailJob($email, $recipient)
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

    private function syncRecipients(): void
    {
        /** @var Email $email */
        $email = $this->record;

        if ($email->status !== EmailStatus::Draft) {
            return;
        }

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
