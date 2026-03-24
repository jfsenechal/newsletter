<?php

declare(strict_types=1);

namespace App\Filament\Resources\Senders\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class SenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->required(),
                Hidden::make('user_id')
                    ->default(fn (): ?int => auth()->id()),
            ]);
    }
}
