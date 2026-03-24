<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressBooks\Pages;

use App\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewAddressBook extends ViewRecord
{
    protected static string $resource = AddressBookResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                RepeatableEntry::make('contacts')
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                RepeatableEntry::make('sharedWithUsers')
                    ->label('Shared With')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
