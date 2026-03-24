<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Schemas;

use App\Models\AddressBook;
use App\Models\Contact;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn (): ?int => auth()->id()),
                Select::make('sender_id')
                    ->relationship('sender', 'name', fn ($query) => $query->where('user_id', auth()->id()))
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->name} <{$record->email}>")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('subject')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('attachments')
                    ->multiple()
                    ->disk('public')
                    ->directory('email-attachments')
                    ->visibility('public')
                    ->columnSpanFull(),
                Select::make('address_book_ids')
                    ->label('Address Books')
                    ->multiple()
                    ->options(fn () => AddressBook::query()
                        ->where('user_id', auth()->id())
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->dehydrated(false),
                Select::make('contact_ids')
                    ->label('Individual Contacts')
                    ->multiple()
                    ->options(fn () => Contact::query()
                        ->where('user_id', auth()->id())
                        ->get()
                        ->mapWithKeys(fn (Contact $contact): array => [
                            $contact->id => "{$contact->first_name} {$contact->last_name} <{$contact->email}>",
                        ]))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->dehydrated(false),
            ]);
    }
}
