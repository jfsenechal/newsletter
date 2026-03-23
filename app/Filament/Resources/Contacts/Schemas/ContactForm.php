<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('last_name')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('first_name')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(65535),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('addressBooks')
                    ->relationship('addressBooks', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
