<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Pages;

use App\Filament\Resources\Emails\EmailResource;
use App\Handler\MailerHandler;
use App\Models\Email;
use Filament\Resources\Pages\EditRecord;

final class EditEmail extends EditRecord
{
    protected static string $resource = EmailResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['address_book_ids'], $data['contact_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        MailerHandler::syncRecipients($this->record, $this->data['address_book_ids'], $this->data['contact_ids']);
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
}
