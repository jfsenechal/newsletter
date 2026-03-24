<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressBooks\Schemas;

use App\Models\Contact;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
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
                Hidden::make('user_id')
                    ->default(fn(): ?int => auth()->id()),
                Select::make('contacts')
                    ->relationship('contacts', 'email')
                    ->getOptionLabelFromRecordUsing(
                        fn(Contact $record): string => "{$record->first_name} {$record->last_name} ({$record->email})"
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('last_name')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('email')
                                ->email()
                                ->unique('contacts', 'email')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(255),
                        ]),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return Contact::query()->create([
                            ...$data,
                            'user_id' => auth()->id(),
                        ])->getKey();
                    }),
                Select::make('sharedWithUsers')
                    ->relationship('sharedWithUsers', 'name')
                    ->getOptionLabelFromRecordUsing(fn(User $record): string => "{$record->name} ({$record->email})")
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
