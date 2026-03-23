<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressBooks\Schemas;

use App\Models\Contact;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class AddressBookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('contacts')
                    ->relationship('contacts', 'email')
                    ->getOptionLabelFromRecordUsing(fn (Contact $record): string => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('sharedWithUsers')
                    ->relationship('sharedWithUsers', 'name')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => "{$record->name} ({$record->email})")
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
